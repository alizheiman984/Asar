<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'otps';

    protected $fillable = [
        'otp',
        'email',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    // Scopes
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Methods
    public function generateCode($length = 6)
    {
        $this->otp = str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        $this->expires_at = now()->addMinutes(5); // Default 5 minutes expiration
        $this->save();
    }

    public function isValid()
    {
        return $this->expires_at > now();
    }

    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function verify($code)
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->otp === $code;
    }

    public function getRemainingTime()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInSeconds($this->expires_at);
    }
} 