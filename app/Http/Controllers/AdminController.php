<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Payment;
use App\Models\SeatChangeRequest;
use App\Models\LeaveApplication;
use App\Models\ExitRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Admin Dashboard ──
    public function dashboard()
    {
        $stats = [
            'total_users'      => User::where('role', 'user')->count(),
            'total_branches'   => Branch::count(),
            'total_rooms'      => Room::count(),
            'pending_requests' => SeatChangeRequest::where('status', 'pending')->count()
                                + LeaveApplication::where('status', 'pending')->count()
                                + ExitRequest::where('status', 'pending')->count(),
            'monthly_revenue'  => Payment::where('status', 'paid')
                                    ->where('month', now()->month)
                                    ->where('year', now()->year)
                                    ->sum('amount'),
            'due_payments'     => Payment::where('status', 'due')->count(),
        ];

        $recentPayments  = Payment::with('user')->latest()->take(10)->get();
        $pendingRequests = SeatChangeRequest::with('user')->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPayments', 'pendingRequests'));
    }

    // ── Users Management ──
    public function users()
    {
        $users    = User::where('role', 'user')->with(['branch', 'room'])->latest()->paginate(20);
        $branches = Branch::with('rooms')->get();

        return view('admin.users', compact('users', 'branches'));
    }

    public function assignUser(Request $request, User $user)
    {
        $request->validate([
            'branch_id'          => 'required|exists:branches,id',
            'room_id'            => 'required|exists:rooms,id',
            'seat_number'        => 'required|integer|min:1',
            'booking_start_date' => 'required|date',
            'booking_end_date'   => 'required|date|after:booking_start_date',
            'deposit'            => 'nullable|numeric|min:0',
        ]);

        $room = Room::findOrFail($request->room_id);

        // Capacity check
        $occupants = User::where('room_id', $room->id)->where('id', '!=', $user->id)->count();
        if ($occupants >= $room->capacity) {
            return back()->with('error', 'Room is at full capacity.');
        }

        $user->update([
            'branch_id'          => $request->branch_id,
            'room_id'            => $request->room_id,
            'seat_number'        => $request->seat_number,
            'booking_start_date' => $request->booking_start_date,
            'booking_end_date'   => $request->booking_end_date,
            'deposit'            => $request->deposit ?? $user->deposit,
        ]);

        $start = \Carbon\Carbon::parse($request->booking_start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($request->booking_end_date)->startOfDay();
        $days = (int) $start->diffInDays($end) + 1;
        
        $totalAmount = round($days * $room->daily_rent, 2);

        // Create initial rent-due record for the current month if one doesn't exist
        $exists = Payment::where('user_id', $user->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('payment_type', 'rent')
            ->exists();

        if (!$exists) {
            Payment::create([
                'user_id'      => $user->id,
                'amount'       => $totalAmount,
                'payment_type' => 'rent',
                'month'        => now()->month,
                'year'         => now()->year,
                'status'       => 'due',
            ]);
        }

        $this->notifyUser(
            $user->id,
            'Room Assigned',
            "You have been assigned to Room {$room->room_number} at {$room->branch->name}.",
            'announcement'
        );

        return back()->with('success', "{$user->name} assigned to Room {$room->room_number}.");
    }

    // ── Rooms & Branches ──
    public function rooms()
    {
        $branches = Branch::with(['rooms' => fn ($q) => $q->withCount('users')])->get();
        return view('admin.rooms', compact('branches'));
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string|max:500',
        ]);

        Branch::create($request->only('name', 'address'));
        return back()->with('success', 'Branch created successfully.');
    }

    public function storeRoom(Request $request)
    {
        $request->validate([
            'branch_id'    => 'required|exists:branches,id',
            'room_number'  => 'required|string|max:50',
            'room_type'    => 'required|string|max:50',
            'capacity'     => 'required|integer|min:1|max:10',
            'daily_rent'   => 'required|numeric|min:0',
        ]);

        Room::create($request->only('branch_id', 'room_number', 'room_type', 'capacity', 'daily_rent'));
        return back()->with('success', 'Room created successfully.');
    }

    public function deleteBranch(Branch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch deleted.');
    }

    public function deleteRoom(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }

    // ── All Requests (Seat Change · Leave · Exit) ──
    public function requests()
    {
        $seatChanges = SeatChangeRequest::with(['user', 'currentRoom', 'requestedRoom', 'currentBranch', 'requestedBranch'])
            ->latest()->paginate(15, ['*'], 'seat_page');

        $leaves = LeaveApplication::with('user')->latest()->paginate(15, ['*'], 'leave_page');
        $exits  = ExitRequest::with('user')->latest()->paginate(15, ['*'], 'exit_page');

        return view('admin.requests', compact('seatChanges', 'leaves', 'exits'));
    }

    public function approveRequest(Request $request, string $type, int $id)
    {
        $note = $request->input('admin_note', '');

        switch ($type) {
            case 'seat_change':
                $req     = SeatChangeRequest::findOrFail($id);
                $req->update(['status' => 'approved', 'admin_note' => $note]);

                $user    = $req->user;
                $newRoom = $req->requestedRoom;

                // Calculate surplus balance: remaining balance + additional paid - new room cost
                // remaining_balance = value left from current room for remaining days
                // additional_paid = extra amount user paid
                // new_room_cost = cost of new room for remaining days
                $totalFunds   = (float) $req->remaining_balance + (float) $user->balance + (float) $req->additional_paid;
                $newRoomCost  = (float) $req->new_room_cost;
                $surplusBalance = max(0, round($totalFunds - $newRoomCost, 2));

                $user->update([
                    'room_id'            => $req->requested_room_id,
                    'branch_id'          => $req->requested_branch_id,
                    'balance'            => $surplusBalance,
                    'booking_start_date' => $req->change_date,
                    // booking_end_date stays the same — user occupies new room for remaining period
                ]);

                $this->notifyUser($user->id, 'Seat Change Approved',
                    "Your seat change request was approved. You are now in Room {$newRoom->room_number} from " .
                    $req->change_date->format('M d, Y') . " to " . $req->booking_end->format('M d, Y') . ".",
                    'seat_change');
                break;

            case 'leave':
                $req = LeaveApplication::findOrFail($id);
                $req->update(['status' => 'approved', 'admin_note' => $note]);
                $this->notifyUser($req->user_id, 'Leave Approved', 'Your leave application has been approved.', 'leave');
                break;

            case 'exit':
                $req = ExitRequest::findOrFail($id);
                $req->update(['status' => 'approved', 'admin_note' => $note]);
                $this->notifyUser($req->user_id, 'Exit Approved', 'Your exit request has been approved. Please complete the settlement.', 'exit');
                break;
        }

        return back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' request approved.');
    }

    public function rejectRequest(Request $request, string $type, int $id)
    {
        $note = $request->input('admin_note', '');

        switch ($type) {
            case 'seat_change':
                SeatChangeRequest::findOrFail($id)->update(['status' => 'rejected', 'admin_note' => $note]);
                $userId = SeatChangeRequest::find($id)->user_id;
                $this->notifyUser($userId, 'Seat Change Rejected', "Your seat change request was rejected. {$note}", 'seat_change');
                break;

            case 'leave':
                LeaveApplication::findOrFail($id)->update(['status' => 'rejected', 'admin_note' => $note]);
                $userId = LeaveApplication::find($id)->user_id;
                $this->notifyUser($userId, 'Leave Rejected', "Your leave application was rejected. {$note}", 'leave');
                break;

            case 'exit':
                ExitRequest::findOrFail($id)->update(['status' => 'rejected', 'admin_note' => $note]);
                $userId = ExitRequest::find($id)->user_id;
                $this->notifyUser($userId, 'Exit Rejected', "Your exit request was rejected. {$note}", 'exit');
                break;
        }

        return back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' request rejected.');
    }

    // ── Announcements ──
    public function announcements()
    {
        $announcements = Notification::where('type', 'announcement')
            ->whereNull('user_id')
            ->latest()->paginate(15);

        return view('admin.announcements', compact('announcements'));
    }

    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Broadcast record (null user_id) for admin history
        Notification::create([
            'user_id' => null,
            'title'   => $request->title,
            'message' => $request->message,
            'type'    => 'announcement',
        ]);

        // Individual notification for every user
        User::where('role', 'user')->each(function (User $u) use ($request) {
            Notification::create([
                'user_id' => $u->id,
                'title'   => $request->title,
                'message' => $request->message,
                'type'    => 'announcement',
            ]);
        });

        $this->emitSocketEvent('announcement', [
            'title'   => $request->title,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Announcement sent to all users.');
    }
}
