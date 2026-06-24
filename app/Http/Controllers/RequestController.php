<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Room;
use App\Models\SeatChangeRequest;
use App\Models\LeaveApplication;
use App\Models\ExitRequest;
use App\Models\Payment;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    // ╔═══════════════════════════════════════════╗
    // ║            SEAT CHANGE                    ║
    // ╚═══════════════════════════════════════════╝

    public function seatChange()
    {
        $user       = auth()->user()->load(['branch', 'room']);
        $branches   = Branch::with('rooms')->get();
        $myRequests = $user->seatChangeRequests()
            ->with(['currentRoom', 'requestedRoom', 'currentBranch', 'requestedBranch'])
            ->latest()->get();

        // Calculate current booking progress
        $bookingData = null;
        if ($user->booking_start_date && $user->booking_end_date) {
            $start = \Carbon\Carbon::parse($user->booking_start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($user->booking_end_date)->startOfDay();
            $today = now()->startOfDay();

            $totalDays = (int) $start->diffInDays($end) + 1;

            if ($today->isBefore($start)) {
                $spentDays = 0;
            } else {
                $spentDays = min($totalDays, (int) $start->diffInDays($today) + 1);
            }

            $remainingDays = max(0, $totalDays - $spentDays);

            // Change date is tomorrow (today counts as a full day in current room)
            $changeDate = $today->copy()->addDay();
            // But if change date goes beyond booking end, cap it
            if ($changeDate->isAfter($end)) {
                $changeDate = $end->copy();
            }

            $bookingData = [
                'start'       => $start->format('M d, Y'),
                'end'         => $end->format('M d, Y'),
                'total'       => $totalDays,
                'spent'       => $spentDays,
                'rem'         => $remainingDays,
                'change_date' => $changeDate->format('M d, Y'),
                'daily_rent'  => $user->room ? $user->room->daily_rent : 0,
            ];
        }

        return view('user.seat-change', compact('user', 'branches', 'myRequests', 'bookingData'));
    }

    public function submitSeatChange(Request $request)
    {
        $user = auth()->user();

        if (!$user->room_id || !$user->branch_id) {
            return back()->with('error', 'You must be assigned to a room before requesting a seat change.');
        }

        if (!$user->booking_start_date || !$user->booking_end_date) {
            return back()->with('error', 'You must have active booking dates to request a seat change.');
        }

        $request->validate(['requested_room_id' => 'required|exists:rooms,id']);

        $currentRoom   = Room::findOrFail($user->room_id);
        $requestedRoom = Room::findOrFail($request->requested_room_id);

        if ($requestedRoom->availableSeats() <= 0) {
            return back()->with('error', 'No seats available in the requested room.');
        }

        // ── Calculate all the per-day values ──
        $start = \Carbon\Carbon::parse($user->booking_start_date)->startOfDay();
        $end   = \Carbon\Carbon::parse($user->booking_end_date)->startOfDay();
        $today = now()->startOfDay();

        $totalDays = (int) $start->diffInDays($end) + 1;

        if ($today->isBefore($start)) {
            $spentDays = 0;
        } else {
            $spentDays = min($totalDays, (int) $start->diffInDays($today) + 1);
        }

        $remainingDays = max(0, $totalDays - $spentDays);

        $spentAmount       = round($spentDays * $currentRoom->daily_rent, 2);
        $remainingBalance  = round($remainingDays * $currentRoom->daily_rent, 2);
        $totalAvailable    = $remainingBalance + (float) $user->balance;
        $newRoomCost       = round($remainingDays * $requestedRoom->daily_rent, 2);

        $additionalNeeded = 0;
        if ($newRoomCost > $totalAvailable) {
            $additionalNeeded = round($newRoomCost - $totalAvailable, 2);
        }

        $coveredDays = 0;
        if ($requestedRoom->daily_rent > 0) {
            $coveredDays = min($remainingDays, (int) floor($totalAvailable / $requestedRoom->daily_rent));
        }

        // Change date (day after today — today is current room's last full day)
        $changeDate = $today->copy()->addDay();
        if ($changeDate->isAfter($end)) {
            $changeDate = $end->copy();
        }

        // Check if additional payment was already made
        $additionalPaid = 0;
        if ($additionalNeeded > 0) {
            // Check for recent seat_change payment by this user
            $recentPayment = Payment::where('user_id', $user->id)
                ->where('payment_type', 'seat_change')
                ->where('status', 'paid')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->latest()
                ->first();

            if ($recentPayment) {
                $additionalPaid = (float) $recentPayment->amount;
            }

            // If additional is needed and not fully paid, block
            if ($additionalPaid < $additionalNeeded) {
                return back()->with('error', 'Please pay the additional amount of ৳' . round($additionalNeeded - $additionalPaid, 2) . ' before submitting.');
            }
        }

        $type           = $currentRoom->branch_id === $requestedRoom->branch_id ? 'same_branch' : 'different_branch';
        $rentDifference = $requestedRoom->daily_rent - $currentRoom->daily_rent;

        SeatChangeRequest::create([
            'user_id'             => $user->id,
            'current_room_id'     => $user->room_id,
            'requested_room_id'   => $request->requested_room_id,
            'current_branch_id'   => $user->branch_id,
            'requested_branch_id' => $requestedRoom->branch_id,
            'type'                => $type,
            'rent_difference'     => $rentDifference,
            'spent_days'          => $spentDays,
            'remaining_days'      => $remainingDays,
            'spent_amount'        => $spentAmount,
            'remaining_balance'   => $remainingBalance,
            'new_room_cost'       => $newRoomCost,
            'additional_needed'   => $additionalNeeded,
            'additional_paid'     => $additionalPaid,
            'covered_days'        => $coveredDays,
            'change_date'         => $changeDate->toDateString(),
            'current_daily_rent'  => $currentRoom->daily_rent,
            'new_daily_rent'      => $requestedRoom->daily_rent,
            'booking_start'       => $user->booking_start_date,
            'booking_end'         => $user->booking_end_date,
        ]);

        $this->notifyAdmins('New Seat Change Request', "{$user->name} requested a seat change.", 'seat_change');

        return back()->with('success', 'Seat change request submitted successfully.');
    }

    /**
     * Process additional payment for seat change (AJAX).
     */
    public function payAdditionalForSeatChange(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:bkash,nagad,visa',
            'amount'         => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        $transactionId = 'SCT' . strtoupper(uniqid());

        $payment = Payment::create([
            'user_id'           => $user->id,
            'amount'            => $request->amount,
            'payment_method'    => $request->payment_method,
            'payment_type'      => 'seat_change',
            'month'             => now()->month,
            'year'              => now()->year,
            'status'            => 'paid',
            'transaction_id'    => $transactionId,
            'stripe_payment_id' => 'sim_' . uniqid(),
        ]);

        // Notify user
        $this->notifyUser(
            $user->id,
            'Seat Change Payment Successful',
            "Your additional payment of ৳{$request->amount} via " . strtoupper($request->payment_method) . " was successful. Transaction: {$transactionId}",
            'payment'
        );

        // Notify admins
        $this->notifyAdmins(
            'Seat Change Payment Received',
            "{$user->name} paid ৳{$request->amount} additional for seat change. Transaction: {$transactionId}",
            'payment'
        );

        $this->emitSocketEvent('payment-update', [
            'user_id' => $user->id,
            'title'   => 'Seat Change Payment',
            'message' => "Additional payment of ৳{$request->amount} received.",
            'status'  => 'paid',
        ]);

        return response()->json([
            'success'        => true,
            'transaction_id' => $transactionId,
            'amount'         => (float) $request->amount,
        ]);
    }

    /**
     * AJAX — return rooms for a given branch with availability info.
     */
    public function getRooms($branchId)
    {
        $rooms = Room::where('branch_id', $branchId)
            ->withCount('users')
            ->get()
            ->map(fn ($room) => [
                'id'              => $room->id,
                'room_number'     => $room->room_number,
                'room_type'       => $room->room_type,
                'capacity'        => $room->capacity,
                'daily_rent'      => $room->daily_rent,
                'available_seats' => $room->capacity - $room->users_count,
            ]);

        return response()->json($rooms);
    }

    /**
     * AJAX — calculate rent difference between current room and a target room based on days.
     */
    public function calculateRentDiff(Request $request)
    {
        $user          = auth()->user();
        $currentRoom   = Room::find($user->room_id);
        $requestedRoom = Room::find($request->room_id);

        if (!$currentRoom || !$requestedRoom || !$user->booking_start_date || !$user->booking_end_date) {
            return response()->json(['error' => 'Invalid room selection or missing booking dates.'], 400);
        }

        $start = \Carbon\Carbon::parse($user->booking_start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($user->booking_end_date)->startOfDay();
        $today = now()->startOfDay();

        $totalDays = (int) $start->diffInDays($end) + 1;

        if ($today->isBefore($start)) {
            $spentDays = 0;
        } else {
            $spentDays = min($totalDays, (int) $start->diffInDays($today) + 1);
        }

        $remainingDays = max(0, $totalDays - $spentDays);

        $spentAmount = round($spentDays * $currentRoom->daily_rent, 2);

        $currentRoomValueLeft = round($remainingDays * $currentRoom->daily_rent, 2);
        $totalAvailable = $currentRoomValueLeft + (float)$user->balance;

        $newRoomCostForRemaining = round($remainingDays * $requestedRoom->daily_rent, 2);

        $additionalNeeded = 0;
        if ($newRoomCostForRemaining > $totalAvailable) {
            $additionalNeeded = round($newRoomCostForRemaining - $totalAvailable, 2);
        }

        $coveredDaysInNewRoom = 0;
        if ($requestedRoom->daily_rent > 0) {
            $coveredDaysInNewRoom = (int) floor($totalAvailable / $requestedRoom->daily_rent);
        }

        // Change date — tomorrow since today is the last full day in current room
        $changeDate = $today->copy()->addDay();
        if ($changeDate->isAfter($end)) {
            $changeDate = $end->copy();
        }

        // Surplus balance (if new room is cheaper)
        $surplusBalance = 0;
        if ($totalAvailable > $newRoomCostForRemaining) {
            $surplusBalance = round($totalAvailable - $newRoomCostForRemaining, 2);
        }

        // Total booking cost (what user originally paid for total days)
        $totalBookingCost = round($totalDays * $currentRoom->daily_rent, 2);

        return response()->json([
            'booking_start'     => $start->format('M d, Y'),
            'booking_end'       => $end->format('M d, Y'),
            'total_days'        => $totalDays,
            'spent_days'        => $spentDays,
            'remaining_days'    => $remainingDays,
            'spent_amount'      => $spentAmount,
            'total_booking_cost'=> $totalBookingCost,
            'current_value_left'=> $currentRoomValueLeft,
            'user_balance'      => (float)$user->balance,
            'total_available'   => $totalAvailable,
            'new_room_cost'     => $newRoomCostForRemaining,
            'additional_needed' => $additionalNeeded,
            'surplus_balance'   => $surplusBalance,
            'covered_days'      => min($remainingDays, $coveredDaysInNewRoom),
            'current_daily'     => $currentRoom->daily_rent,
            'new_daily'         => $requestedRoom->daily_rent,
            'is_upgrade'        => $requestedRoom->daily_rent > $currentRoom->daily_rent,
            'change_date'       => $changeDate->format('M d, Y'),
            'remaining_start'   => $changeDate->format('M d, Y'),
            'remaining_end'     => $end->format('M d, Y'),
            'current_room_spent_period' => $start->format('M d, Y') . ' - ' . $today->format('M d, Y'),
            'new_room_period'   => $changeDate->format('M d, Y') . ' - ' . $end->format('M d, Y'),
        ]);
    }

    // ╔═══════════════════════════════════════════╗
    // ║          LEAVE APPLICATION                 ║
    // ╚═══════════════════════════════════════════╝

    public function leave()
    {
        $myLeaves = auth()->user()->leaveApplications()->latest()->get();
        return view('user.leave', compact('myLeaves'));
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
            'reason'     => 'required|string|max:500',
        ]);

        LeaveApplication::create([
            'user_id'    => auth()->id(),
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'reason'     => $request->reason,
        ]);

        $this->notifyAdmins('New Leave Application', auth()->user()->name . ' applied for leave.', 'leave');

        return back()->with('success', 'Leave application submitted successfully.');
    }

    // ╔═══════════════════════════════════════════╗
    // ║            EXIT REQUEST                    ║
    // ╚═══════════════════════════════════════════╝

    public function exit()
    {
        $user       = auth()->user()->load(['room', 'branch']);
        $myExits    = $user->exitRequests()->latest()->get();
        $settlement = $this->calculateSettlement($user);

        return view('user.exit', compact('user', 'myExits', 'settlement'));
    }

    public function submitExit(Request $request)
    {
        $request->validate([
            'reason'    => 'required|string|max:500',
            'exit_date' => 'required|date|after:+29 days',
        ]);

        $user       = auth()->user();
        $settlement = $this->calculateSettlement($user);

        ExitRequest::create([
            'user_id'        => $user->id,
            'reason'         => $request->reason,
            'notice_date'    => now()->toDateString(),
            'exit_date'      => $request->exit_date,
            'total_due'      => $settlement['total_due'],
            'deposit_refund' => $settlement['deposit_refund'],
            'final_amount'   => $settlement['final_amount'],
        ]);

        $this->notifyAdmins('New Exit Request', "{$user->name} requested to exit.", 'exit');

        return back()->with('success', 'Exit request submitted successfully.');
    }

    // ── Settlement Calculator ──

    private function calculateSettlement($user): array
    {
        $pendingRent = Payment::where('user_id', $user->id)->where('status', 'due')->sum('amount');
        $balance     = (float) $user->balance;
        $deposit     = (float) $user->deposit;

        $totalDue      = max(0, $pendingRent - $balance);
        $depositRefund = max(0, $deposit - $totalDue);
        $finalAmount   = $totalDue - $deposit; // positive = user owes, negative = refundable

        return compact('pendingRent', 'balance', 'deposit', 'totalDue', 'depositRefund', 'finalAmount');
    }
}
