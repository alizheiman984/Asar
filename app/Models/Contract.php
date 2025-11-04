<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'image',
        'company_name',
        'contract_date',
        'team_id'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class);
    }
} 