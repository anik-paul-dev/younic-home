<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // ── Payment History & Current Rent Status ──
    public function index()
    {
        $user = auth()->user()->load(['room', 'branch']);

        $payments = $user->payments()->latest()->paginate(15);

        $currentRent = 0;
        if ($user->room && $user->booking_start_date && $user->booking_end_date) {
            $start = \Carbon\Carbon::parse($user->booking_start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($user->booking_end_date)->startOfDay();
            $totalDays = (int) $start->diffInDays($end) + 1;
            $currentRent = round($totalDays * $user->room->daily_rent, 2);
        }

        $currentPayment = $user->payments()
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('payment_type', 'rent')
            ->first();

        $rentStatus = $currentPayment ? $currentPayment->status : 'due';

        // Payable amount = rent minus any positive balance
        $payable = max(0, $currentRent - (float) $user->balance);

        return view('user.rent', compact('user', 'payments', 'currentRent', 'rentStatus', 'payable', 'currentPayment'));
    }

    // ── Process a Payment (Simulated Stripe in test mode) ──
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:bkash,nagad,visa',
            'amount'         => 'required|numeric|min:1',
            'payment_type'   => 'required|in:rent,seat_change,deposit',
            'transaction_id' => 'nullable|string',
        ]);

        $user  = auth()->user();
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        // Prevent duplicate rent payment for the same month
        if ($request->payment_type === 'rent') {
            $alreadyPaid = Payment::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('payment_type', 'rent')
                ->where('status', 'paid')
                ->exists();

            if ($alreadyPaid) {
                return back()->with('error', 'Rent for this month is already paid.');
            }
        }

        $transactionId = $request->transaction_id ?: 'TXN' . strtoupper(uniqid());

        $existingDue = null;
        if ($request->payment_type === 'rent') {
            $existingDue = Payment::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('payment_type', 'rent')
                ->where('status', 'due')
                ->first();
        }

        if ($existingDue) {
            $existingDue->update([
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'status'           => 'paid',
                'transaction_id'   => $transactionId,
                'stripe_payment_id'=> 'sim_' . uniqid(),
            ]);
            $payment = $existingDue;
        } else {
            $payment = Payment::create([
                'user_id'          => $user->id,
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'payment_type'     => $request->payment_type,
                'month'            => $month,
                'year'             => $year,
                'status'           => 'paid',
                'transaction_id'   => $transactionId,
                'stripe_payment_id'=> 'sim_' . uniqid(),
            ]);
        }

        // Deduct balance if applicable
        if ((float) $user->balance > 0 && $request->payment_type === 'rent') {
            $user->balance = 0;
            $user->save();
        }

        // Notify user
        $this->notifyUser(
            $user->id,
            'Payment Successful',
            "Your payment of ৳{$request->amount} via " . strtoupper($request->payment_method) . " was successful. Transaction: {$transactionId}",
            'payment'
        );

        // Notify admins
        $this->notifyAdmins(
            'New Payment Received',
            "{$user->name} paid ৳{$request->amount} via " . strtoupper($request->payment_method) . ". Transaction: {$transactionId}",
            'payment'
        );

        // Real-time payment status update
        $this->emitSocketEvent('payment-update', [
            'user_id' => $user->id,
            'title'   => 'Payment Successful',
            'message' => "Payment of ৳{$request->amount} received.",
            'status'  => 'paid',
        ]);

        return back()->with('success', "Payment of ৳{$request->amount} processed successfully! Transaction ID: {$transactionId}");
    }
}
