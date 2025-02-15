<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            $table->integer('replies_count')->default(0)->after('views');
            $table->index(['forum_id', 'last_activity_at']);
        });

        // Populate existing data
        DB::statement("
            UPDATE threads
            SET last_activity_at = (
                SELECT GREATEST(
                    COALESCE(
                        (SELECT MAX(created_at)
                        FROM replies
                        WHERE thread_id = threads.id
                        AND deleted_at IS NULL),
                        threads.created_at
                    ),
                    threads.created_at
                )
            ),
            replies_count = (
                SELECT COUNT(*)
                FROM replies
                WHERE thread_id = threads.id
                AND deleted_at IS NULL
            )
        ");
    }

    public function down()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
            $table->dropColumn('replies_count');
            $table->dropIndex(['forum_id', 'last_activity_at']);
        });
    }
};
