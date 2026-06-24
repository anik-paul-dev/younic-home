<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'payment_method', 'payment_type',
        'month', 'year', 'status', 'transaction_id', 'stripe_payment_id',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'due');
    }
}
