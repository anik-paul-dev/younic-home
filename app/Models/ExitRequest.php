<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitRequest extends Model
{
    protected $fillable = [
        'user_id', 'reason', 'notice_date', 'exit_date',
        'total_due', 'deposit_refund', 'final_amount',
        'status', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'notice_date'    => 'date',
            'exit_date'      => 'date',
            'total_due'      => 'decimal:2',
            'deposit_refund' => 'decimal:2',
            'final_amount'   => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
