<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplie extends Model
{
    use HasFactory;

     protected $fillable = [
        'employee_id',
        'item_id',
        'quantity',
        'type',
        'campaign_id',
        'notes'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
