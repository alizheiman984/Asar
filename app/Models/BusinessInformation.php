<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInformation extends Model
{
    use HasFactory;

    protected $table ='business_informations';

    protected $fillable = [
        'team_id',
        'team_name',
        'license_number',
        'address',
        'bank_account_number',
        'log_image',
        'logo',
        'long',
        'lat',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class,'team_id');
    }
} 