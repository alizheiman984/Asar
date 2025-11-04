<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'number_of_volunteer',
        'cost',
        'address',
        'from',
        'to',
        'points',
        'status',
        'specialization_id',
        'campaign_type_id',
        'team_id',
        'employee_id'
    ];

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function campaignType(): BelongsTo
    {
        return $this->belongsTo(CampaignType::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function campaignVolunteers(): HasMany
    {
        return $this->hasMany(CampaignVolunteer::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(Volunteer::class, 'campaign_volunteers');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
} 