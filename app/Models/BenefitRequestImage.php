<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitRequestImage extends Model
{
    use HasFactory;
      protected $fillable = ['benefit_request_id', 'image_path'];

    public function request()
    {
        return $this->belongsTo(BenefitRequests::class);
    }
    
}
