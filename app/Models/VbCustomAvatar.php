<?php

namespace App\Models;

use App\Services\AvatarUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbCustomAvatar extends Model
{
    use HasFactory;

    protected $table = 'vb_custom_avatars';

    protected $primaryKey = 'userid';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(VbUser::class, 'userid', 'userid');
    }

    /**
     * Build the public URL for a legacy vBulletin avatar filename.
     */
    public static function urlForFilename(string $filename): string
    {
        $prefix = trim((string) config('app.archive_avatar_path', 'storage/avatars/archive'), '/');

        return AvatarUrl::fromPath($prefix.'/'.rawurlencode($filename));
    }

    public function getUrlAttribute(): string
    {
        return self::urlForFilename($this->filename);
    }
}
