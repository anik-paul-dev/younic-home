<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Notifications
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllRead'])->name('notifications.read.all');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markRead'])->name('notifications.read');

    // Seat Change
    Route::get('/seat-change', [RequestController::class, 'seatChange'])->name('seat-change');
    Route::post('/seat-change', [RequestController::class, 'submitSeatChange'])->name('seat-change.submit');
    Route::get('/api/branches/{branch}/rooms', [RequestController::class, 'getRooms']);
    Route::post('/api/calculate-rent-diff', [RequestController::class, 'calculateRentDiff']);
    Route::post('/api/seat-change-payment', [RequestController::class, 'payAdditionalForSeatChange']);

    // Leave Application
    Route::get('/leave', [RequestController::class, 'leave'])->name('leave');
    Route::post('/leave', [RequestController::class, 'submitLeave'])->name('leave.submit');

    // Exit Request
    Route::get('/exit', [RequestController::class, 'exit'])->name('exit');
    Route::post('/exit', [RequestController::class, 'submitExit'])->name('exit.submit');

    // Rent & Payments
    Route::get('/rent', [PaymentController::class, 'index'])->name('rent');
    Route::post('/pay-rent', [PaymentController::class, 'processPayment'])->name('pay-rent');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/assign', [AdminController::class, 'assignUser'])->name('users.assign');
    
    // Branches & Rooms
    Route::get('/rooms', [AdminController::class, 'rooms'])->name('rooms');
    Route::post('/branches', [AdminController::class, 'storeBranch'])->name('branches.store');
    Route::delete('/branches/{branch}', [AdminController::class, 'deleteBranch'])->name('branches.destroy');
    Route::post('/rooms', [AdminController::class, 'storeRoom'])->name('rooms.store');
    Route::delete('/rooms/{room}', [AdminController::class, 'deleteRoom'])->name('rooms.destroy');
    
    // Requests
    Route::get('/requests', [AdminController::class, 'requests'])->name('requests');
    Route::post('/requests/{type}/{id}/approve', [AdminController::class, 'approveRequest'])->name('requests.approve');
    Route::post('/requests/{type}/{id}/reject', [AdminController::class, 'rejectRequest'])->name('requests.reject');
    
    // Announcements
    Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
    Route::post('/announcements', [AdminController::class, 'sendAnnouncement'])->name('announcements.send');
});
