<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeVerification extends Model
{
    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'verified',
        'verified_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public static function recordVerification(string $sessionId, string $ip, ?string $userAgent): self
    {
        return self::create([
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'verified' => true,
            'verified_at' => now(),
        ]);
    }
}