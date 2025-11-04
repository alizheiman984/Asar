<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
} 