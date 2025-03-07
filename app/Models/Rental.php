<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory;

    protected $table = 'rentals';

    protected $fillable = [
        'tool_id',
        'user_id',
        'start_date',
        'end_date',
        'total_cost',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_cost' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
} 