@extends('layouts.app')
@section('title', 'Dashboard')
@section('header_title', 'Resident Dashboard')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Profile & Room Status -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Room Info Card -->
        <div class="glass-card p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-500/10 rounded-full blur-2xl -mr-10 -mt-10 transition duration-500 group-hover:bg-teal-500/20"></div>
            
            <h3 class="text-lg font-semibold text-white mb-4">Current Residence</h3>
            @if($user->room)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Branch</p>
                        <p class="font-medium text-slate-200">{{ $user->branch->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Room No.</p>
                        <p class="font-medium text-slate-200">{{ $user->room->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Room Type</p>
                        <p class="font-medium text-slate-200">{{ $user->room->room_type }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Seat No.</p>
                        <p class="font-medium text-slate-200">{{ $user->seat_number }}</p>
                    </div>
                </div>
            @else
                <div class="text-amber-400 bg-amber-500/10 p-4 rounded-lg border border-amber-500/20">
                    <p class="font-medium">You have not been assigned a room yet.</p>
                    <p class="text-sm mt-1">Please wait for the admin to process your admission.</p>
                </div>
            @endif
        </div>

        <!-- Update Profile Card -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Personal Information</h3>
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="input-field" required>
                    </div>
                    <div>
                        <label class="form-label">Email (Read Only)</label>
                        <input type="email" value="{{ $user->email }}" class="input-field opacity-50 cursor-not-allowed" disabled>
                    </div>
                    <div>
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="input-field" required>
                    </div>
                    <div>
                        <label class="form-label">NID / ID Number</label>
                        <input type="text" name="nid" value="{{ $user->nid }}" class="input-field">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
        
    </div>

    <!-- Right Sidebar Items -->
    <div class="space-y-6">
        
        <!-- Financial Summary -->
        <div class="glass-card p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl -mr-10 -mt-10 transition duration-500 group-hover:bg-amber-500/20"></div>
            
            <h3 class="text-lg font-semibold text-white mb-4">Financial Summary</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-slate-700/50">
                    <span class="text-slate-400">Current Month Rent</span>
                    @if($currentPayment)
                        @if($currentPayment->status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @else
                            <span class="badge badge-danger">Due: ৳{{ $currentPayment->amount }}</span>
                        @endif
                    @else
                        <span class="text-slate-200">Not assigned</span>
                    @endif
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-slate-700/50">
                    <span class="text-slate-400">Wallet Balance</span>
                    <span class="font-semibold text-teal-400">৳{{ $user->balance }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Security Deposit</span>
                    <span class="font-semibold text-slate-200">৳{{ $user->deposit }}</span>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="{{ route('rent') }}" class="btn-outline w-full block text-center">View Payment History</a>
            </div>
        </div>

    </div>
</div>
@endsection
