@extends('layouts.app')
@section('title', 'Leave Application')
@section('header_title', 'Leave Application')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Leave Form -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Apply for Leave</h3>
        
        <form action="{{ route('leave.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="input-field" min="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="input-field" min="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            
            <div>
                <label class="form-label">Reason for Leave</label>
                <textarea name="reason" rows="4" class="input-field resize-none" placeholder="Please specify the reason..." required></textarea>
            </div>
            
            <button type="submit" class="w-full btn-primary mt-2">Submit Application</button>
        </form>
    </div>

    <!-- History -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Application History</h3>
        
        @if($myLeaves->isEmpty())
            <div class="text-slate-400 text-center py-8">
                No leave applications submitted yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($myLeaves as $leave)
                    <div class="p-4 bg-slate-900/40 rounded-lg border border-slate-700">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-sm text-slate-300">
                                <span class="font-medium">{{ $leave->start_date->format('M d, Y') }}</span>
                                <span class="text-slate-500 mx-2">to</span>
                                <span class="font-medium">{{ $leave->end_date->format('M d, Y') }}</span>
                            </div>
                            <span class="badge {{ 
                                $leave->status === 'approved' ? 'badge-success' : 
                                ($leave->status === 'rejected' ? 'badge-danger' : 'badge-warning') 
                            }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2 line-clamp-2">"{{ $leave->reason }}"</p>
                        @if($leave->admin_note)
                            <div class="mt-2 text-xs p-2 bg-slate-800 rounded border border-slate-700 text-slate-300">
                                <strong>Admin Note:</strong> {{ $leave->admin_note }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
