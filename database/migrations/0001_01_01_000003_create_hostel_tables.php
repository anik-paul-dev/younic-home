<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Branches ──
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->timestamps();
        });

        // ── Rooms ──
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 50);
            $table->string('room_type', 50);
            $table->unsignedTinyInteger('capacity');
            $table->decimal('daily_rent', 10, 2);
            $table->timestamps();

            $table->unique(['branch_id', 'room_number']);
            $table->index('room_type');
        });

        // ── FK constraints on users for branch & room ──
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });

        // ── Payments ──
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['bkash', 'nagad', 'visa'])->default('bkash');
            $table->enum('payment_type', ['rent', 'seat_change', 'deposit'])->default('rent');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['paid', 'due', 'pending'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('stripe_payment_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'month', 'year']);
            $table->index('status');
        });

        // ── Seat Change Requests ──
        Schema::create('seat_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('current_room_id');
            $table->unsignedBigInteger('requested_room_id');
            $table->unsignedBigInteger('current_branch_id');
            $table->unsignedBigInteger('requested_branch_id');
            $table->enum('type', ['same_branch', 'different_branch'])->default('same_branch');
            $table->decimal('rent_difference', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('current_room_id')->references('id')->on('rooms');
            $table->foreign('requested_room_id')->references('id')->on('rooms');
            $table->foreign('current_branch_id')->references('id')->on('branches');
            $table->foreign('requested_branch_id')->references('id')->on('branches');
            $table->index('status');
        });

        // ── Leave Applications ──
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // ── Exit Requests ──
        Schema::create('exit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->date('notice_date');
            $table->date('exit_date');
            $table->decimal('total_due', 10, 2)->default(0);
            $table->decimal('deposit_refund', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'settled'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // ── Notifications ──
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['rent_reminder', 'seat_change', 'leave', 'exit', 'announcement', 'payment'])->default('announcement');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'is_read']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('exit_requests');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('seat_change_requests');
        Schema::dropIfExists('payments');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['room_id']);
        });
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('branches');
    }
};
