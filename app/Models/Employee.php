<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, HasApiTokens ,Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'national_number',
        'position',
        'phone',
        'address',
        'date_accession',
        'image',
        'team_id',
        'specialization_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class,'team_id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function donorPayments(): HasMany
    {
        return $this->hasMany(DonorPayment::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }
} 