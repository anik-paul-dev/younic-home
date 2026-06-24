@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('header_title', 'Admin Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="glass-card p-4">
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Users</h4>
        <div class="text-2xl font-bold text-teal-400">{{ $stats['total_users'] }}</div>
    </div>
    <div class="glass-card p-4">
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Branches</h4>
        <div class="text-2xl font-bold text-teal-400">{{ $stats['total_branches'] }}</div>
    </div>
    <div class="glass-card p-4">
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Rooms</h4>
        <div class="text-2xl font-bold text-teal-400">{{ $stats['total_rooms'] }}</div>
    </div>
    <div class="glass-card p-4 border border-amber-500/30 bg-amber-500/5">
        <h4 class="text-xs font-semibold text-amber-500/70 uppercase tracking-wider mb-1">Pending Req.</h4>
        <div class="text-2xl font-bold text-amber-500">{{ $stats['pending_requests'] }}</div>
    </div>
    <div class="glass-card p-4 border border-emerald-500/30 bg-emerald-500/5">
        <h4 class="text-xs font-semibold text-emerald-500/70 uppercase tracking-wider mb-1">Rev. (Month)</h4>
        <div class="text-xl font-bold text-emerald-400">৳{{ number_format($stats['monthly_revenue'], 0) }}</div>
    </div>
    <div class="glass-card p-4 border border-red-500/30 bg-red-500/5">
        <h4 class="text-xs font-semibold text-red-500/70 uppercase tracking-wider mb-1">Due Payments</h4>
        <div class="text-2xl font-bold text-red-400">{{ $stats['due_payments'] }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Recent Payments -->
    <div class="lg:col-span-2 glass-card p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">Recent Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-2 px-2">User</th>
                        <th class="py-2 px-2">Type</th>
                        <th class="py-2 px-2">Amount</th>
                        <th class="py-2 px-2">Date</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($recentPayments as $payment)
                        <tr class="border-b border-slate-800">
                            <td class="py-3 px-2 text-slate-200">
                                {{ $payment->user->name }}
                                <div class="text-xs text-slate-500">{{ $payment->user->phone }}</div>
                            </td>
                            <td class="py-3 px-2 text-slate-400 capitalize">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                            <td class="py-3 px-2 text-teal-400 font-medium">৳{{ $payment->amount }}</td>
                            <td class="py-3 px-2 text-slate-400 text-xs">{{ $payment->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-slate-500">No recent payments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Seat Changes -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Recent Seat Requests</h3>
        <div class="space-y-3">
            @forelse($pendingRequests as $req)
                <div class="p-3 bg-slate-900/50 rounded-lg border border-amber-500/20">
                    <div class="flex justify-between mb-1">
                        <span class="font-medium text-slate-200">{{ $req->user->name }}</span>
                        <span class="text-xs text-amber-500">Pending</span>
                    </div>
                    <p class="text-xs text-slate-400">Req. Room: {{ $req->requestedRoom->room_number ?? 'N/A' }}</p>
                    <a href="{{ route('admin.requests') }}" class="text-xs text-teal-400 hover:underline mt-2 inline-block">Review Request &rarr;</a>
                </div>
            @empty
                <div class="text-slate-500 text-center py-4 text-sm">No pending seat change requests.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
