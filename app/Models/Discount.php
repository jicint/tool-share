<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'usage_limit',
        'times_used',
        'valid_from',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function isValid()
    {
        $now = now();
        return $this->is_active &&
            $now->gte($this->valid_from) &&
            ($this->valid_until === null || $now->lte($this->valid_until)) &&
            ($this->usage_limit === null || $this->times_used < $this->usage_limit);
    }
} 