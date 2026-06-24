<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['branch_id', 'room_number', 'room_type', 'capacity', 'daily_rent'];

    protected function casts(): array
    {
        return ['daily_rent' => 'decimal:2'];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Returns the number of unoccupied seats in this room.
     */
    public function availableSeats(): int
    {
        return $this->capacity - $this->users()->count();
    }
}
