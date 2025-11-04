<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Campaign;
use App\Models\Benefactor;
use App\Models\Employee;
use App\Models\VolunteerTeam;

class DonorPayment extends Model
{
    use HasFactory;

    protected $guarded = []; 


    public function volunter(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class,'volunteer_id');
    }


    protected $casts = [
       
        'amount' => 'decimal:2'
    ];

    public function benefactor(): BelongsTo
    {
        return $this->belongsTo(Benefactor::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class);
    }
} 