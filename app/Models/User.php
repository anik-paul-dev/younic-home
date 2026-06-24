<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'nid', 'role',
        'branch_id', 'room_id', 'seat_number', 'booking_start_date', 'booking_end_date', 'balance', 'deposit', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'balance'            => 'decimal:2',
            'deposit'            => 'decimal:2',
            'booking_start_date' => 'date',
            'booking_end_date'   => 'date',
        ];
    }

    // ── Helpers ──

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ── Relationships ──

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function seatChangeRequests()
    {
        return $this->hasMany(SeatChangeRequest::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function exitRequests()
    {
        return $this->hasMany(ExitRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
