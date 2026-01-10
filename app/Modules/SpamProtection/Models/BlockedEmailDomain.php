<?php

namespace App\Modules\SpamProtection\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedEmailDomain extends Model
{
    protected $fillable = [
        'domain',
        'reason',
        'type',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public static function isBlocked(string $email): bool
    {
        $domain = static::extractDomain($email);

        return static::query()
            ->where('is_active', true)
            ->where('domain', $domain)
            ->exists();
    }

    public static function getBlockedDomain(string $email): ?static
    {
        $domain = static::extractDomain($email);

        return static::query()
            ->where('is_active', true)
            ->where('domain', $domain)
            ->first();
    }

    protected static function extractDomain(string $email): string
    {
        return strtolower(substr(strrchr($email, '@'), 1));
    }
}
