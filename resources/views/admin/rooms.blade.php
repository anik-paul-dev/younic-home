@extends('layouts.app')
@section('title', 'Branches & Rooms')
@section('header_title', 'Branches & Rooms')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Add New Branch / Room Forms -->
    <div class="space-y-6">
        
        <!-- Branch Form -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Add New Branch</h3>
            <form action="{{ route('admin.branches.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="name" class="input-field" required placeholder="e.g. Younic Home - Uttara">
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" class="input-field resize-none" rows="2" required></textarea>
                </div>
                <button type="submit" class="w-full btn-primary">Create Branch</button>
            </form>
        </div>

        <!-- Room Form -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Add New Room</h3>
            <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="input-field" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="input-field" required>
                    </div>
                    <div>
                        <label class="form-label">Capacity (Seats)</label>
                        <input type="number" name="capacity" class="input-field" min="1" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Type</label>
                        <input type="text" name="room_type" class="input-field" placeholder="e.g. 2-Seat" required>
                    </div>
                    <div>
                        <label class="form-label">Daily Rent (৳)</label>
                        <input type="number" name="daily_rent" class="input-field" min="0" required>
                    </div>
                </div>
                <button type="submit" class="w-full btn-primary">Create Room</button>
            </form>
        </div>

    </div>

    <!-- Branch/Room List -->
    <div class="lg:col-span-2 space-y-6">
        @foreach($branches as $branch)
            <div class="glass-card overflow-hidden">
                <div class="bg-slate-800/80 p-4 border-b border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold text-white">{{ $branch->name }}</h3>
                        <p class="text-xs text-slate-400">{{ $branch->address }}</p>
                    </div>
                    <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('Delete this branch and all its rooms?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete Branch</button>
                    </form>
                </div>
                
                <div class="p-4">
                    @if($branch->rooms->isEmpty())
                        <p class="text-sm text-slate-500">No rooms in this branch yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($branch->rooms as $room)
                                <div class="p-3 border border-slate-700 rounded-lg bg-slate-900/30 flex justify-between items-center">
                                    <div>
                                        <div class="font-medium text-slate-200">Room {{ $room->room_number }} <span class="text-xs text-slate-400">({{ $room->room_type }})</span></div>
                                        <div class="text-xs text-slate-400 mt-1">
                                            Rent: ৳{{ $room->daily_rent }}/day | 
                                            Occupancy: {{ $room->users_count }}/{{ $room->capacity }}
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this room?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-500 hover:text-red-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
