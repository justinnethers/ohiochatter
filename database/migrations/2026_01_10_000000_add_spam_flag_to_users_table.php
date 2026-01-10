<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_flagged_spam')->default(false)->after('is_moderator');
            $table->string('spam_flag_reason')->nullable()->after('is_flagged_spam');
            $table->timestamp('spam_flagged_at')->nullable()->after('spam_flag_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_flagged_spam', 'spam_flag_reason', 'spam_flagged_at']);
        });
    }
};
