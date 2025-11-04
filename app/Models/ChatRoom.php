<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

protected $guarded = []; 

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

  
    public function campaign()
    {
        return $this->belongsTo(Campaign::class)->select('id', 'campaign_name');
    }


    public function volunteers()
    {
        return $this->belongsToMany(Volunteer::class, 'chat_room_users', 'chat_room_id', 'user_id')
                    ->wherePivot('user_type', 'App\\Models\\Volunteer')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'chat_room_users', 'chat_room_id', 'user_id')
                    ->withPivot('user_type', 'App\\Models\\Employee')
                    ->withTimestamps();
    }


  

}
