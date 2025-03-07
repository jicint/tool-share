<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'user_id',
        'amount',
        'security_deposit',
        'late_fees',
        'discount_amount',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime'
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 