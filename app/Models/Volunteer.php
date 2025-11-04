<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Volunteer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    

    protected $fillable = [
        'full_name',
        'national_number',
        'nationality',
        'phone',
        'email',
        'password',
        'image',
        'total_points',
        'specialization_id',
        'birth_date',
     
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];



    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function campaignVolunteers(): HasMany
    {
        return $this->hasMany(CampaignVolunteer::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_volunteers');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_volunteers');
    }
} 