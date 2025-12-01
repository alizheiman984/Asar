<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

     protected $fillable = ['name', 'unit'];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function supplies()
    {
        return $this->hasMany(Supplie::class);
    }
}
