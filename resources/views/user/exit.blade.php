@extends('layouts.app')
@section('title', 'Exit Request')
@section('header_title', 'Exit Request')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Request Form -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Request to Leave Hostel</h3>
        <p class="text-sm text-slate-400 mb-6">A minimum 30-day notice period is required.</p>
        
        <div class="mb-6 p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg">
            <h4 class="font-medium text-amber-400 mb-2">Refund Policy Reminder</h4>
            <p class="text-xs text-amber-200/70 leading-relaxed">
                As per Younic Home policy, if you cancel your seat before the notice period or leave early, no rent will be refunded. Your security deposit will be adjusted against any due rent.
            </p>
        </div>

        <form action="{{ route('exit.submit') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Notice Date</label>
                    <input type="text" value="{{ date('Y-m-d') }}" class="input-field opacity-50 cursor-not-allowed" disabled>
                </div>
                <div>
                    <label class="form-label">Expected Exit Date</label>
                    <input type="date" name="exit_date" class="input-field" min="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                </div>
            </div>
            
            <div>
                <label class="form-label">Reason for Leaving</label>
                <textarea name="reason" rows="3" class="input-field resize-none" placeholder="Please tell us why you are leaving..." required></textarea>
            </div>
            
            <!-- Dynamic Settlement Preview -->
            <div class="p-4 bg-slate-900 border border-slate-700 rounded-lg mt-4">
                <h4 class="text-sm font-semibold text-slate-200 mb-3">Estimated Final Settlement</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pending Rent & Dues:</span>
                        <span class="text-red-400">৳{{ $settlement['pendingRent'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Wallet Balance:</span>
                        <span class="text-teal-400">৳{{ $settlement['balance'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Security Deposit:</span>
                        <span class="text-emerald-400">৳{{ $settlement['deposit'] }}</span>
                    </div>
                    <hr class="border-slate-700 my-2">
                    <div class="flex justify-between font-bold">
                        @if($settlement['finalAmount'] > 0)
                            <span class="text-slate-200">Amount You Owe:</span>
                            <span class="text-red-400">৳{{ $settlement['finalAmount'] }}</span>
                        @elseif($settlement['finalAmount'] < 0)
                            <span class="text-slate-200">Amount to Refund:</span>
                            <span class="text-emerald-400">৳{{ abs($settlement['finalAmount']) }}</span>
                        @else
                            <span class="text-slate-200">Final Settlement:</span>
                            <span class="text-slate-300">৳0 (Cleared)</span>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full btn-danger mt-4">Submit Exit Request</button>
        </form>
    </div>

    <!-- History -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Exit Requests</h3>
        
        @if($myExits->isEmpty())
            <div class="text-slate-400 text-center py-8">
                No exit requests submitted.
            </div>
        @else
            <div class="space-y-4">
                @foreach($myExits as $req)
                    <div class="p-4 bg-slate-900/40 rounded-lg border border-slate-700">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-sm text-slate-300">
                                <p><strong>Exit Date:</strong> {{ $req->exit_date->format('M d, Y') }}</p>
                            </div>
                            <span class="badge {{ 
                                $req->status === 'approved' ? 'badge-info' : 
                                ($req->status === 'settled' ? 'badge-success' : 
                                ($req->status === 'rejected' ? 'badge-danger' : 'badge-warning')) 
                            }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-400 space-y-1 mt-2">
                            <p><strong>Notice Date:</strong> {{ $req->notice_date->format('M d, Y') }}</p>
                            <p><strong>Settlement:</strong> {{ $req->final_amount > 0 ? 'Owe ৳'.$req->final_amount : 'Refund ৳'.abs($req->final_amount) }}</p>
                        </div>
                        @if($req->admin_note)
                            <div class="mt-2 text-xs p-2 bg-slate-800 rounded border border-slate-700 text-slate-300">
                                <strong>Admin Note:</strong> {{ $req->admin_note }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
