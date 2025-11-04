<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Benefactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone'
    ];

    public function donorPayments(): HasMany
    {
        return $this->hasMany(DonorPayment::class);
    }
} 