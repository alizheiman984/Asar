<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Financial extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_amount',
        'payment',
        'team_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment' => 'decimal:2'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class);
    }
} 