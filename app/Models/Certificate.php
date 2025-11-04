<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $guarded = []; 


    public function volunter(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class,'volunteer_id');
    }


}
