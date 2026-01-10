<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mark all existing users who have posted as verified so they
     * are not affected by the new email verification requirement.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->where(function ($query) {
                $query->where('post_count', '>', 0)
                      ->orWhere('posts_old', '>', 0);
            })
            ->update(['email_verified_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible - we can't know which users were verified by this migration
    }
};
