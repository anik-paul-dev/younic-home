<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seat_change_requests', function (Blueprint $table) {
            $table->integer('spent_days')->default(0)->after('rent_difference');
            $table->integer('remaining_days')->default(0)->after('spent_days');
            $table->decimal('spent_amount', 10, 2)->default(0)->after('remaining_days');
            $table->decimal('remaining_balance', 10, 2)->default(0)->after('spent_amount');
            $table->decimal('new_room_cost', 10, 2)->default(0)->after('remaining_balance');
            $table->decimal('additional_needed', 10, 2)->default(0)->after('new_room_cost');
            $table->decimal('additional_paid', 10, 2)->default(0)->after('additional_needed');
            $table->integer('covered_days')->default(0)->after('additional_paid');
            $table->date('change_date')->nullable()->after('covered_days');
            $table->decimal('current_daily_rent', 10, 2)->default(0)->after('change_date');
            $table->decimal('new_daily_rent', 10, 2)->default(0)->after('current_daily_rent');
            $table->date('booking_start')->nullable()->after('new_daily_rent');
            $table->date('booking_end')->nullable()->after('booking_start');
        });
    }

    public function down(): void
    {
        Schema::table('seat_change_requests', function (Blueprint $table) {
            $table->dropColumn([
                'spent_days', 'remaining_days', 'spent_amount', 'remaining_balance',
                'new_room_cost', 'additional_needed', 'additional_paid', 'covered_days',
                'change_date', 'current_daily_rent', 'new_daily_rent',
                'booking_start', 'booking_end',
            ]);
        });
    }
};
