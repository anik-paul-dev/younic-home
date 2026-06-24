@extends('layouts.app')
@section('title', 'Manage Requests')
@section('header_title', 'Manage Requests')

@section('content')

<!-- AlpineJS is useful for tabs, but we'll use vanilla JS for simplicity -->
<div class="mb-6 border-b border-slate-700">
    <nav class="flex space-x-8" aria-label="Tabs" id="request-tabs">
        <button class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm border-teal-500 text-teal-400 transition" data-target="seat-changes">
            Seat Changes
            @if($seatChanges->where('status', 'pending')->count() > 0)
                <span class="ml-2 bg-red-500 text-white py-0.5 px-2 rounded-full text-xs">{{ $seatChanges->where('status', 'pending')->count() }}</span>
            @endif
        </button>
        <button class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300 transition" data-target="leaves">
            Leave Applications
            @if($leaves->where('status', 'pending')->count() > 0)
                <span class="ml-2 bg-red-500 text-white py-0.5 px-2 rounded-full text-xs">{{ $leaves->where('status', 'pending')->count() }}</span>
            @endif
        </button>
        <button class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300 transition" data-target="exits">
            Exit Requests
            @if($exits->where('status', 'pending')->count() > 0)
                <span class="ml-2 bg-red-500 text-white py-0.5 px-2 rounded-full text-xs">{{ $exits->where('status', 'pending')->count() }}</span>
            @endif
        </button>
    </nav>
</div>

<!-- Seat Changes -->
<div id="seat-changes" class="tab-content block">
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-800/50 border-b border-slate-700 text-slate-400 text-sm">
                <tr>
                    <th class="py-3 px-4">Resident</th>
                    <th class="py-3 px-4">Request Detail</th>
                    <th class="py-3 px-4">Days</th>
                    <th class="py-3 px-4">Financial</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-800/50">
                @forelse($seatChanges as $req)
                    <tr>
                        <td class="py-3 px-4">{{ $req->user->name }}</td>
                        <td class="py-3 px-4">
                            From: {{ $req->currentBranch->name }} (R:{{ $req->currentRoom->room_number }})<br>
                            To: {{ $req->requestedBranch->name }} (R:{{ $req->requestedRoom->room_number }})
                            @if($req->change_date)
                                <div class="text-xs text-purple-400 mt-1">Change: {{ $req->change_date->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs">
                            @if($req->spent_days || $req->remaining_days)
                                <span class="text-amber-400">Spent: {{ $req->spent_days }}d</span><br>
                                <span class="text-teal-400">Remain: {{ $req->remaining_days }}d</span>
                            @else
                                <span class="text-slate-500">N/A</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs">
                            @if($req->remaining_balance || $req->new_room_cost)
                                <span class="text-slate-300">Bal: ৳{{ $req->remaining_balance }}</span><br>
                                <span class="text-slate-300">New: ৳{{ $req->new_room_cost }}</span>
                                @if($req->additional_needed > 0)
                                    <br><span class="text-red-400">+৳{{ $req->additional_needed }}</span>
                                    <span class="{{ $req->additional_paid >= $req->additional_needed ? 'text-emerald-400' : 'text-amber-400' }}">
                                        ({{ $req->additional_paid >= $req->additional_needed ? 'Paid' : 'Unpaid' }})
                                    </span>
                                @endif
                            @else
                                @if($req->rent_difference > 0) <span class="text-red-400">+৳{{ $req->rent_difference }}</span>
                                @elseif($req->rent_difference < 0) <span class="text-teal-400">৳{{ $req->rent_difference }}</span>
                                @else ৳0 @endif
                            @endif
                        </td>
                        <td class="py-3 px-4"><span class="badge {{ $req->status == 'pending' ? 'badge-warning' : ($req->status == 'approved' ? 'badge-success' : 'badge-danger') }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="py-3 px-4">
                            @if($req->status === 'pending')
                                <button onclick="openActionModal('seat_change', {{ $req->id }}, 'Approve/Reject Seat Change for {{ $req->user->name }}')" class="btn-outline text-xs py-1 px-2">Action</button>
                            @else
                                <span class="text-slate-500 italic text-xs">Processed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-500">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-700 bg-slate-800/20">{{ $seatChanges->appends(request()->except('seat_page'))->links('pagination::tailwind') }}</div>
    </div>
</div>

<!-- Leaves -->
<div id="leaves" class="tab-content hidden">
    <div class="glass-card overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-800/50 border-b border-slate-700 text-slate-400 text-sm">
                <tr>
                    <th class="py-3 px-4">Resident</th>
                    <th class="py-3 px-4">Dates</th>
                    <th class="py-3 px-4">Reason</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-800/50">
                @forelse($leaves as $req)
                    <tr>
                        <td class="py-3 px-4">{{ $req->user->name }}</td>
                        <td class="py-3 px-4">{{ $req->start_date->format('M d') }} - {{ $req->end_date->format('M d') }}</td>
                        <td class="py-3 px-4 max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                        <td class="py-3 px-4"><span class="badge {{ $req->status == 'pending' ? 'badge-warning' : ($req->status == 'approved' ? 'badge-success' : 'badge-danger') }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="py-3 px-4">
                            @if($req->status === 'pending')
                                <button onclick="openActionModal('leave', {{ $req->id }}, 'Approve/Reject Leave for {{ $req->user->name }}')" class="btn-outline text-xs py-1 px-2">Action</button>
                            @else
                                <span class="text-slate-500 italic text-xs">Processed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-500">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-700 bg-slate-800/20">{{ $leaves->appends(request()->except('leave_page'))->links('pagination::tailwind') }}</div>
    </div>
</div>

<!-- Exits -->
<div id="exits" class="tab-content hidden">
    <div class="glass-card overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-800/50 border-b border-slate-700 text-slate-400 text-sm">
                <tr>
                    <th class="py-3 px-4">Resident</th>
                    <th class="py-3 px-4">Exit Date</th>
                    <th class="py-3 px-4">Settlement</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-800/50">
                @forelse($exits as $req)
                    <tr>
                        <td class="py-3 px-4">{{ $req->user->name }}</td>
                        <td class="py-3 px-4">{{ $req->exit_date->format('M d, Y') }}</td>
                        <td class="py-3 px-4">
                            @if($req->final_amount > 0)
                                <span class="text-red-400">Owes ৳{{ $req->final_amount }}</span>
                            @else
                                <span class="text-teal-400">Refund ৳{{ abs($req->final_amount) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4"><span class="badge {{ $req->status == 'pending' ? 'badge-warning' : ($req->status == 'approved' ? 'badge-success' : 'badge-danger') }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="py-3 px-4">
                            @if($req->status === 'pending')
                                <button onclick="openActionModal('exit', {{ $req->id }}, 'Approve/Reject Exit for {{ $req->user->name }}')" class="btn-outline text-xs py-1 px-2">Action</button>
                            @else
                                <span class="text-slate-500 italic text-xs">Processed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-500">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-700 bg-slate-800/20">{{ $exits->appends(request()->except('exit_page'))->links('pagination::tailwind') }}</div>
    </div>
</div>

<!-- Action Modal -->
<div id="actionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="glass-card w-full max-w-md p-6 relative">
        <button type="button" onclick="closeActionModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <h3 id="modalTitle" class="text-lg font-semibold text-white mb-4"></h3>
        
        <form id="approveForm" method="POST" action="" class="hidden">@csrf</form>
        <form id="rejectForm" method="POST" action="" class="hidden">@csrf</form>

        <div class="space-y-4">
            <div>
                <label class="form-label">Admin Note (Optional, visible to user)</label>
                <textarea id="adminNote" rows="3" class="input-field resize-none"></textarea>
            </div>
            
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="submitAction('approve')" class="flex-1 btn-primary bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20">Approve Request</button>
                <button type="button" onclick="submitAction('reject')" class="flex-1 btn-danger">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching logic
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-teal-500', 'text-teal-400');
                b.classList.add('border-transparent', 'text-slate-400');
            });
            btn.classList.add('border-teal-500', 'text-teal-400');
            btn.classList.remove('border-transparent', 'text-slate-400');
            
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById(btn.dataset.target).classList.remove('hidden');
        });
    });

    // Modal Logic
    let currentType = '';
    let currentId = '';

    function openActionModal(type, id, title) {
        currentType = type;
        currentId = id;
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('adminNote').value = '';
        document.getElementById('actionModal').classList.remove('hidden');
    }

    function closeActionModal() {
        document.getElementById('actionModal').classList.add('hidden');
    }

    function submitAction(action) {
        const note = document.getElementById('adminNote').value;
        const form = document.getElementById(action + 'Form');
        form.action = `/admin/requests/${currentType}/${currentId}/${action}`;
        
        const noteInput = document.createElement('input');
        noteInput.type = 'hidden';
        noteInput.name = 'admin_note';
        noteInput.value = note;
        form.appendChild(noteInput);
        
        form.submit();
    }
</script>
@endsection
