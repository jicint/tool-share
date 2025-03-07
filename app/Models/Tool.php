<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'condition',
        'daily_rate',
        'user_id'
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'availability_status' => 'boolean'
    ];

    protected $primaryKey = 'id';

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(ToolImage::class);
    }
} 