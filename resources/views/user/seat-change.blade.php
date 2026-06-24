@extends('layouts.app')
@section('title', 'Seat Change')
@section('header_title', 'Seat Change Request')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Request Form -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Request a Seat Change</h3>
        
        @if(!$user->room_id)
            <div class="text-amber-400 bg-amber-500/10 p-4 rounded-lg border border-amber-500/20">
                You cannot request a seat change because you haven't been assigned a room yet.
            </div>
        @elseif(!$user->booking_start_date || !$user->booking_end_date)
            <div class="text-amber-400 bg-amber-500/10 p-4 rounded-lg border border-amber-500/20">
                You cannot request a seat change because you don't have active booking dates.
            </div>
        @else
            <form action="{{ route('seat-change.submit') }}" method="POST" id="seat-change-form">
                @csrf
                <div class="space-y-5">
                    
                    <!-- Current Room Info -->
                    <div class="p-4 bg-slate-900/50 rounded-lg border border-slate-700">
                        <p class="text-sm text-slate-400 mb-2">Current Room:</p>
                        <p class="font-medium text-slate-200">{{ $user->branch->name }} - Room {{ $user->room->room_number }} ({{ $user->room->room_type }})</p>
                        <p class="text-sm text-teal-400 mt-1">Daily Rent: ৳<span id="current-rent-val">{{ number_format($user->room->daily_rent, 2) }}</span></p>
                        
                        @if($bookingData)
                        <div class="mt-3 pt-3 border-t border-slate-700 text-sm">
                            <div class="flex justify-between text-slate-300">
                                <span>Booking Period:</span>
                                <span>{{ $bookingData['start'] }} → {{ $bookingData['end'] }}</span>
                            </div>
                            <div class="flex justify-between text-slate-300 mt-1">
                                <span>Total Booked Days:</span>
                                <span>{{ $bookingData['total'] }} days</span>
                            </div>
                            <div class="flex justify-between text-slate-300 mt-1">
                                <span>Days Spent (incl. today):</span>
                                <span class="text-amber-400 font-medium">{{ $bookingData['spent'] }} days</span>
                            </div>
                            <div class="flex justify-between text-slate-300 mt-1">
                                <span>Remaining Days:</span>
                                <span class="text-teal-400 font-medium">{{ $bookingData['rem'] }} days</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Target Branch -->
                    <div>
                        <label class="form-label">Select Target Branch</label>
                        <select id="branch-select" class="input-field" required>
                            <option value="">-- Choose Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target Room -->
                    <div>
                        <label class="form-label">Select Target Room</label>
                        <select name="requested_room_id" id="room-select" class="input-field" disabled required>
                            <option value="">-- First choose a branch --</option>
                        </select>
                    </div>

                    <!-- Calculation Result (hidden until room selected) -->
                    <div id="calc-result" class="hidden">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Additional Payment Section (hidden until needed) -->
                    <div id="additional-payment-section" class="hidden">
                        <!-- Populated by JS -->
                    </div>

                    <button type="submit" id="submit-btn" class="w-full btn-primary" disabled>Submit Seat Change Request</button>
                </div>
            </form>
        @endif
    </div>

    <!-- Request History -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">My Requests</h3>
        
        @if($myRequests->isEmpty())
            <div class="text-slate-400 text-center py-8">
                No seat change requests made yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($myRequests as $req)
                    <div class="p-4 bg-slate-900/40 rounded-lg border border-slate-700">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center text-sm text-slate-300">
                                <span class="font-medium">{{ $req->currentRoom->room_number ?? 'N/A' }}</span>
                                <svg class="w-4 h-4 mx-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <span class="font-medium text-teal-400">{{ $req->requestedRoom->room_number ?? 'N/A' }}</span>
                            </div>
                            <span class="badge {{ 
                                $req->status === 'approved' ? 'badge-success' : 
                                ($req->status === 'rejected' ? 'badge-danger' : 'badge-warning') 
                            }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </div>
                        
                        <!-- Detailed Info -->
                        <div class="text-xs text-slate-400 space-y-1 mt-2">
                            <div class="flex justify-between">
                                <span>To: {{ $req->requestedBranch->name ?? 'N/A' }}</span>
                                <span>Type: {{ str_replace('_', ' ', ucfirst($req->type)) }}</span>
                            </div>
                            @if($req->booking_start && $req->booking_end)
                            <div class="flex justify-between">
                                <span>Booked: {{ $req->booking_start->format('M d') }} → {{ $req->booking_end->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($req->change_date)
                            <div class="flex justify-between">
                                <span>Change Date: {{ $req->change_date->format('M d, Y') }}</span>
                                <span>Spent: {{ $req->spent_days }} days | Remaining: {{ $req->remaining_days }} days</span>
                            </div>
                            @endif
                            <div class="flex justify-between mt-1 pt-1 border-t border-slate-800">
                                <span>Current: ৳{{ $req->current_daily_rent }}/day</span>
                                <span>New: ৳{{ $req->new_daily_rent }}/day</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Remaining Balance: ৳{{ $req->remaining_balance }}</span>
                                <span>New Cost: ৳{{ $req->new_room_cost }}</span>
                            </div>
                            @if($req->additional_needed > 0)
                            <div class="flex justify-between text-red-400">
                                <span>Additional Needed: ৳{{ $req->additional_needed }}</span>
                                <span>Paid: ৳{{ $req->additional_paid }}</span>
                            </div>
                            @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch-select');
    const roomSelect = document.getElementById('room-select');
    const calcResult = document.getElementById('calc-result');
    const paymentSection = document.getElementById('additional-payment-section');
    const submitBtn = document.getElementById('submit-btn');
    let additionalPaid = false;
    let additionalNeededAmount = 0;

    if(branchSelect) {
        branchSelect.addEventListener('change', async function() {
            roomSelect.innerHTML = '<option value="">Loading...</option>';
            roomSelect.disabled = true;
            calcResult.classList.add('hidden');
            calcResult.innerHTML = '';
            paymentSection.classList.add('hidden');
            paymentSection.innerHTML = '';
            submitBtn.disabled = true;
            additionalPaid = false;

            if (!this.value) return;

            const res = await fetch(`/api/branches/${this.value}/rooms`);
            const rooms = await res.json();

            roomSelect.innerHTML = '<option value="">-- Choose Room --</option>';
            rooms.forEach(room => {
                const available = room.available_seats > 0;
                roomSelect.innerHTML += `<option value="${room.id}" ${!available ? 'disabled' : ''}>
                    Room ${room.room_number} (${room.room_type}) — ৳${parseFloat(room.daily_rent).toFixed(2)}/day 
                    [${room.available_seats} seats left]
                </option>`;
            });
            roomSelect.disabled = false;
        });

        roomSelect.addEventListener('change', async function() {
            if(!this.value) {
                calcResult.classList.add('hidden');
                calcResult.innerHTML = '';
                paymentSection.classList.add('hidden');
                paymentSection.innerHTML = '';
                submitBtn.disabled = true;
                additionalPaid = false;
                return;
            }

            const formData = new FormData();
            formData.append('room_id', this.value);
            formData.append('_token', '{{ csrf_token() }}');

            const res = await fetch('/api/calculate-rent-diff', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.error) {
                calcResult.innerHTML = `<div class="p-4 rounded-lg border bg-red-500/10 border-red-500/30 text-red-400 text-sm">${data.error}</div>`;
                calcResult.classList.remove('hidden');
                submitBtn.disabled = true;
                return;
            }

            additionalNeededAmount = data.additional_needed;

            // Build the calculation display
            let html = `
            <div class="p-5 rounded-lg border bg-slate-800/60 border-slate-600 text-sm space-y-4">
                <h4 class="font-semibold text-blue-400 text-base flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Seat Change Calculation
                </h4>

                <!-- Booking Period -->
                <div class="p-3 bg-slate-900/60 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider mb-2 font-medium">Booking Period</p>
                    <div class="flex justify-between text-slate-300">
                        <span>Original Period:</span>
                        <span class="font-medium">${data.booking_start} → ${data.booking_end}</span>
                    </div>
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>Total Booked:</span>
                        <span>${data.total_days} days</span>
                    </div>
                </div>

                <!-- Days Breakdown -->
                <div class="p-3 bg-slate-900/60 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider mb-2 font-medium">Days Breakdown</p>
                    <div class="flex justify-between text-slate-300">
                        <span>Days Spent in Current Room:</span>
                        <span class="font-medium text-amber-400">${data.spent_days} days</span>
                    </div>
                    <div class="text-xs text-slate-500 text-right">(${data.current_room_spent_period})</div>
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>Remaining Days for New Room:</span>
                        <span class="font-medium text-teal-400">${data.remaining_days} days</span>
                    </div>
                    <div class="text-xs text-slate-500 text-right">(${data.new_room_period})</div>
                    <div class="flex justify-between text-slate-300 mt-2 pt-2 border-t border-slate-700">
                        <span>Change / Move Date:</span>
                        <span class="font-medium text-purple-400">${data.change_date}</span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="p-3 bg-slate-900/60 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider mb-2 font-medium">Financial Breakdown</p>
                    
                    <div class="text-xs text-slate-500 uppercase tracking-wider mt-1 mb-1">Current Room</div>
                    <div class="flex justify-between text-slate-300">
                        <span>Daily Rate:</span>
                        <span>৳${parseFloat(data.current_daily).toFixed(2)}/day</span>
                    </div>
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>Amount Spent (${data.spent_days} days):</span>
                        <span class="text-amber-400">৳${parseFloat(data.spent_amount).toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>Remaining Value (${data.remaining_days} days):</span>
                        <span class="text-teal-400 font-medium">৳${parseFloat(data.current_value_left).toFixed(2)}</span>
                    </div>
                    ${data.user_balance > 0 ? `
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>+ Wallet Balance:</span>
                        <span class="text-teal-400">৳${parseFloat(data.user_balance).toFixed(2)}</span>
                    </div>` : ''}
                    <div class="flex justify-between text-slate-200 mt-2 pt-2 border-t border-slate-700 font-semibold">
                        <span>Total Available Balance:</span>
                        <span class="text-teal-400">৳${parseFloat(data.total_available).toFixed(2)}</span>
                    </div>

                    <hr class="border-slate-700 my-3">
                    
                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">New Room</div>
                    <div class="flex justify-between text-slate-300">
                        <span>Daily Rate:</span>
                        <span>৳${parseFloat(data.new_daily).toFixed(2)}/day</span>
                    </div>
                    <div class="flex justify-between text-slate-300 mt-1">
                        <span>Cost for ${data.remaining_days} days:</span>
                        <span class="font-medium ${data.is_upgrade ? 'text-red-400' : 'text-teal-400'}">৳${parseFloat(data.new_room_cost).toFixed(2)}</span>
                    </div>
                </div>

                <!-- Result -->
                <div class="p-3 rounded-lg border ${data.additional_needed > 0 ? 'bg-red-500/10 border-red-500/30' : 'bg-emerald-500/10 border-emerald-500/30'}">
                    ${data.additional_needed > 0 ? `
                        <div class="flex justify-between text-base font-semibold">
                            <span class="text-slate-200">Additional Payment Required:</span>
                            <span class="text-red-400">৳${parseFloat(data.additional_needed).toFixed(2)}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            Your available balance of ৳${parseFloat(data.total_available).toFixed(2)} covers 
                            <strong class="text-amber-400">${data.covered_days} out of ${data.remaining_days} days</strong> 
                            in the new room. You need to pay ৳${parseFloat(data.additional_needed).toFixed(2)} more to cover all ${data.remaining_days} remaining days.
                        </p>
                    ` : `
                        <div class="flex justify-between text-base font-semibold">
                            <span class="text-slate-200">Surplus Balance After Change:</span>
                            <span class="text-emerald-400">৳${parseFloat(data.surplus_balance).toFixed(2)}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            Your available balance fully covers all <strong class="text-teal-400">${data.remaining_days} days</strong> 
                            in the new room. The surplus of ৳${parseFloat(data.surplus_balance).toFixed(2)} will be credited to your wallet balance.
                        </p>
                    `}
                </div>

                <!-- Days Coverage Bar -->
                <div class="p-3 bg-slate-900/60 rounded-lg border border-slate-700">
                    <p class="text-xs text-slate-400 mb-2">New Room Coverage</p>
                    <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-500 ${data.covered_days >= data.remaining_days ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-amber-500 to-red-500'}" 
                             style="width: ${data.remaining_days > 0 ? Math.min(100, (data.covered_days / data.remaining_days) * 100) : 0}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1 text-right">
                        ${data.covered_days} / ${data.remaining_days} days covered by current balance
                    </p>
                </div>
            </div>`;

            calcResult.innerHTML = html;
            calcResult.classList.remove('hidden');

            // Show additional payment form if needed
            if (data.additional_needed > 0) {
                showAdditionalPaymentForm(data.additional_needed);
                submitBtn.disabled = true;
            } else {
                paymentSection.classList.add('hidden');
                paymentSection.innerHTML = '';
                submitBtn.disabled = false;
                additionalPaid = false;
            }
        });
    }

    function showAdditionalPaymentForm(amount) {
        const html = `
        <div class="p-5 rounded-lg border bg-red-500/5 border-red-500/20">
            <h4 class="font-semibold text-red-400 text-sm mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pay Additional Amount: ৳${parseFloat(amount).toFixed(2)}
            </h4>
            
            <div class="space-y-3">
                <div>
                    <label class="form-label text-xs">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="additional_payment_method" value="bkash" class="peer sr-only" checked>
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-pink-500/20 peer-checked:border-pink-500 peer-checked:text-pink-400 transition text-sm">
                                bKash
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="additional_payment_method" value="nagad" class="peer sr-only">
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-orange-500/20 peer-checked:border-orange-500 peer-checked:text-orange-400 transition text-sm">
                                Nagad
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="additional_payment_method" value="visa" class="peer sr-only">
                            <div class="text-center py-2 px-1 border border-slate-700 rounded-lg peer-checked:bg-blue-500/20 peer-checked:border-blue-500 peer-checked:text-blue-400 transition text-sm">
                                Visa/Master
                            </div>
                        </label>
                    </div>
                </div>
                
                <button type="button" id="pay-additional-btn" onclick="processAdditionalPayment(${amount})" class="w-full py-2.5 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white font-medium rounded-lg transition-all duration-200 text-sm flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Pay ৳${parseFloat(amount).toFixed(2)} Now
                </button>
            </div>

            <!-- Payment success message (hidden initially) -->
            <div id="payment-success" class="hidden mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-lg">
                <div class="flex items-center text-emerald-400 text-sm">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span id="payment-success-msg">Payment successful!</span>
                </div>
            </div>
        </div>`;

        paymentSection.innerHTML = html;
        paymentSection.classList.remove('hidden');
    }

    // Make processAdditionalPayment globally accessible
    window.processAdditionalPayment = async function(amount) {
        const payBtn = document.getElementById('pay-additional-btn');
        const method = document.querySelector('input[name="additional_payment_method"]:checked')?.value || 'bkash';
        
        payBtn.disabled = true;
        payBtn.innerHTML = `<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...`;

        try {
            const formData = new FormData();
            formData.append('payment_method', method);
            formData.append('amount', amount);
            formData.append('_token', '{{ csrf_token() }}');

            const res = await fetch('/api/seat-change-payment', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                // Hide payment form, show success
                payBtn.classList.add('hidden');
                document.querySelectorAll('input[name="additional_payment_method"]').forEach(r => r.closest('label').style.display = 'none');
                document.querySelector('#additional-payment-section .form-label')?.closest('div')?.classList.add('hidden');
                
                const successEl = document.getElementById('payment-success');
                successEl.classList.remove('hidden');
                document.getElementById('payment-success-msg').innerHTML = 
                    `Payment of ৳${parseFloat(data.amount).toFixed(2)} successful! Transaction: ${data.transaction_id}`;
                
                // Enable submit button
                additionalPaid = true;
                submitBtn.disabled = false;
            } else {
                payBtn.disabled = false;
                payBtn.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> Retry Payment`;
                alert('Payment failed. Please try again.');
            }
        } catch(err) {
            payBtn.disabled = false;
            payBtn.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> Retry Payment`;
            alert('Payment error. Please try again.');
        }
    };
});
</script>
@endsection
