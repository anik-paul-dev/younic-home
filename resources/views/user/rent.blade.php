@extends('layouts.app')
@section('title', 'Rent & Payments')
@section('header_title', 'Rent & Payments')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Payment Form -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Current Status -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Current Month Status</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Rent for Booked Period:</span>
                    <span class="font-medium">৳{{ $currentRent }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Wallet Balance:</span>
                    <span class="font-medium text-teal-400">৳{{ $user->balance }}</span>
                </div>
                <hr class="border-slate-700">
                <div class="flex justify-between items-center text-lg">
                    <span class="text-slate-300">Total Payable:</span>
                    <span class="font-bold text-red-400">৳{{ $payable }}</span>
                </div>
                
                <div class="mt-4">
                    @if($rentStatus === 'paid')
                        <div class="w-full py-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center rounded-lg font-medium">
                            Rent for this month is Paid
                        </div>
                    @elseif($payable <= 0)
                        <div class="w-full py-3 bg-teal-500/10 border border-teal-500/30 text-teal-400 text-center rounded-lg font-medium text-sm">
                            Rent is covered by your wallet balance.<br>Next month it will be adjusted automatically.
                        </div>
                        <form action="{{ route('pay-rent') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="payment_method" value="wallet">
                            <input type="hidden" name="amount" value="{{ $currentRent }}">
                            <input type="hidden" name="payment_type" value="rent">
                            <button type="submit" class="w-full btn-outline text-sm">Settle Now via Balance</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Make Payment -->
        @if($rentStatus !== 'paid' && $payable > 0)
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Make Payment</h3>
            <form action="{{ route('pay-rent') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_type" value="rent">
                <input type="hidden" name="amount" value="{{ $payable }}">

                <div>
                    <label class="form-label">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="bkash" class="peer sr-only" checked>
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-pink-500/20 peer-checked:border-pink-500 peer-checked:text-pink-400 transition">
                                bKash
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="nagad" class="peer sr-only">
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-orange-500/20 peer-checked:border-orange-500 peer-checked:text-orange-400 transition">
                                Nagad
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="visa" class="peer sr-only">
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-blue-500/20 peer-checked:border-blue-500 peer-checked:text-blue-400 transition">
                                Visa/Master
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Simulated Stripe Card Element (Visual only for test) -->
                <div id="card-element-container" class="hidden pt-2">
                    <label class="form-label">Card Details</label>
                    <div class="p-3 bg-slate-900 border border-slate-700 rounded-lg text-slate-400 text-sm flex items-center justify-center">
                        [Stripe Test Element Simulated]
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full btn-primary py-3 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Pay ৳{{ $payable }} Now
                    </button>
                    <p class="text-xs text-center text-slate-500 mt-3">This is a simulated test payment environment.</p>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Payment History -->
    <div class="lg:col-span-2 glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Payment History</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-sm">
                        <th class="py-3 px-2 font-medium">Date</th>
                        <th class="py-3 px-2 font-medium">Type</th>
                        <th class="py-3 px-2 font-medium">Method</th>
                        <th class="py-3 px-2 font-medium">Amount</th>
                        <th class="py-3 px-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($payments as $payment)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">
                            <td class="py-3 px-2 text-slate-300">
                                {{ $payment->created_at->format('M d, Y') }}
                                <div class="text-xs text-slate-500">{{ $payment->transaction_id }}</div>
                            </td>
                            <td class="py-3 px-2 text-slate-300 capitalize">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                            <td class="py-3 px-2 text-slate-300 uppercase">{{ $payment->payment_method }}</td>
                            <td class="py-3 px-2 font-medium text-slate-200">৳{{ $payment->amount }}</td>
                            <td class="py-3 px-2">
                                <span class="badge {{ $payment->status === 'paid' ? 'badge-success' : ($payment->status === 'due' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No payment history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $payments->links('pagination::tailwind') }}
        </div>
    </div>

</div>

<script>
    // Simple toggle to show/hide dummy card input if visa is selected
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const container = document.getElementById('card-element-container');
            if(e.target.value === 'visa') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
    });
</script>
@endsection
