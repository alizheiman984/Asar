<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'volunteer_id',
        'employee_id',
        'description',
        'status',
        'supervisor_note'
    ];


    public function images()
    {
        return $this->hasMany(BenefitRequestImage::class, 'benefit_request_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id');
    }

      public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


}
