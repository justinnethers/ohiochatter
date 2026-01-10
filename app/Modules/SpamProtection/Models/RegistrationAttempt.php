<?php

namespace App\Modules\SpamProtection\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationAttempt extends Model
{
    protected $fillable = [
        'ip_address',
        'email',
        'username',
        'user_agent',
        'status',
        'block_reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function getRecentAttemptsByIp(string $ip, int $minutes = 60): int
    {
        return static::query()
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public static function getRecentBlockedByIp(string $ip, int $minutes = 60): int
    {
        return static::query()
            ->where('ip_address', $ip)
            ->where('status', '!=', 'success')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', '!=', 'success');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }
}
