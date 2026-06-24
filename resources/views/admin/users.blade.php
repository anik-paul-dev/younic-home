@extends('layouts.app')
@section('title', 'Manage Users')
@section('header_title', 'Manage Users')

@section('content')
<div class="glass-card p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-white">Resident List</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-sm">
                    <th class="py-3 px-2 font-medium">Name & Info</th>
                    <th class="py-3 px-2 font-medium">Branch</th>
                    <th class="py-3 px-2 font-medium">Room</th>
                    <th class="py-3 px-2 font-medium">Finances</th>
                    <th class="py-3 px-2 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach($users as $user)
                    <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">
                        <td class="py-3 px-2">
                            <div class="font-medium text-slate-200">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->phone }} | {{ $user->email }}</div>
                        </td>
                        <td class="py-3 px-2 text-slate-300">{{ $user->branch->name ?? 'Not Assigned' }}</td>
                        <td class="py-3 px-2 text-slate-300">
                            {{ $user->room->room_number ?? 'N/A' }} 
                            @if($user->seat_number)
                                (Seat: {{ $user->seat_number }})
                            @endif
                        </td>
                        <td class="py-3 px-2">
                            <div class="text-xs">
                                <span class="text-teal-400">Bal: ৳{{ $user->balance }}</span><br>
                                <span class="text-emerald-400">Dep: ৳{{ $user->deposit }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-2">
                            <button type="button" class="text-teal-400 hover:text-teal-300 text-sm font-medium" onclick="openAssignModal({{ $user->id }}, '{{ $user->name }}')">
                                Assign Room
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $users->links('pagination::tailwind') }}
    </div>
</div>

<!-- Assign Room Modal -->
<div id="assignModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="glass-card w-full max-w-md p-6 relative">
        <button type="button" onclick="closeAssignModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <h3 class="text-lg font-semibold text-white mb-4">Assign Room to <span id="modalUserName" class="text-teal-400"></span></h3>
        
        <form id="assignForm" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id" id="branchSelect" class="input-field" required onchange="loadRooms(this.value)">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Room</label>
                    <select name="room_id" id="roomSelect" class="input-field" required disabled>
                        <option value="">-- First select a branch --</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Booking Start Date</label>
                        <input type="date" name="booking_start_date" class="input-field" required>
                    </div>
                    <div>
                        <label class="form-label">Booking End Date</label>
                        <input type="date" name="booking_end_date" class="input-field" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Seat Number</label>
                        <input type="number" name="seat_number" class="input-field" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Add Deposit (৳)</label>
                        <input type="number" name="deposit" class="input-field" min="0" value="0">
                    </div>
                </div>
                
                <button type="submit" class="w-full btn-primary mt-4">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<script>
    const roomsData = @json($branches->keyBy('id')->map(fn($b) => $b->rooms));

    function openAssignModal(userId, userName) {
        document.getElementById('assignModal').classList.remove('hidden');
        document.getElementById('modalUserName').innerText = userName;
        document.getElementById('assignForm').action = `/admin/users/${userId}/assign`;
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    function loadRooms(branchId) {
        const roomSelect = document.getElementById('roomSelect');
        roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
        
        if (branchId && roomsData[branchId]) {
            roomsData[branchId].forEach(room => {
                roomSelect.innerHTML += `<option value="${room.id}">Room ${room.room_number} (${room.room_type} - ৳${room.daily_rent}/day)</option>`;
            });
            roomSelect.disabled = false;
        } else {
            roomSelect.disabled = true;
        }
    }
</script>
@endsection
