<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = ['user_id', 'start_date', 'end_date', 'reason', 'status', 'admin_note'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
