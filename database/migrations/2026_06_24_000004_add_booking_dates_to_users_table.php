<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('booking_start_date')->nullable()->after('seat_number');
            $table->date('booking_end_date')->nullable()->after('booking_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['booking_start_date', 'booking_end_date']);
        });
    }
};
