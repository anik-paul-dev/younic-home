<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatChangeRequest extends Model
{
    protected $fillable = [
        'user_id', 'current_room_id', 'requested_room_id',
        'current_branch_id', 'requested_branch_id',
        'type', 'rent_difference',
        'spent_days', 'remaining_days', 'spent_amount', 'remaining_balance',
        'new_room_cost', 'additional_needed', 'additional_paid', 'covered_days',
        'change_date', 'current_daily_rent', 'new_daily_rent',
        'booking_start', 'booking_end',
        'status', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'rent_difference'   => 'decimal:2',
            'spent_amount'      => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'new_room_cost'     => 'decimal:2',
            'additional_needed' => 'decimal:2',
            'additional_paid'   => 'decimal:2',
            'current_daily_rent'=> 'decimal:2',
            'new_daily_rent'    => 'decimal:2',
            'change_date'       => 'date',
            'booking_start'     => 'date',
            'booking_end'       => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentRoom()
    {
        return $this->belongsTo(Room::class, 'current_room_id');
    }

    public function requestedRoom()
    {
        return $this->belongsTo(Room::class, 'requested_room_id');
    }

    public function currentBranch()
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }

    public function requestedBranch()
    {
        return $this->belongsTo(Branch::class, 'requested_branch_id');
    }
}
