<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // ── User Dashboard ──
    public function index()
    {
        $user = auth()->user()->load(['branch', 'room', 'payments' => fn ($q) => $q->latest()->take(5)]);

        $notifications = Notification::forUser(auth()->id())->latest()->take(10)->get();
        $unreadCount   = Notification::forUser(auth()->id())->unread()->count();

        // Current-month rent status
        $currentPayment = $user->payments()
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('payment_type', 'rent')
            ->first();

        return view('user.dashboard', compact('user', 'notifications', 'unreadCount', 'currentPayment'));
    }

    // ── Update Profile ──
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'nid'   => 'nullable|string|max:50',
        ]);

        auth()->user()->update($request->only('name', 'phone', 'nid'));

        return back()->with('success', 'Profile updated successfully.');
    }

    // ── Mark Single Notification Read (AJAX) ──
    public function markRead($id)
    {
        Notification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // ── Mark All Notifications Read ──
    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
