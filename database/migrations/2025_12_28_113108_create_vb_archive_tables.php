<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * These tables already exist in production (imported from vBulletin).
     * This migration only creates them for the test database.
     */
    public function up(): void
    {
        if (!Schema::hasTable('vb_forums')) {
            Schema::create('vb_forums', function (Blueprint $table) {
                $table->smallInteger('forumid', true, true);
                $table->string('title', 100)->default('');
                $table->string('title_clean', 100)->default('');
                $table->text('description')->nullable();
                $table->integer('threadcount')->unsigned()->default(0);
                $table->integer('replycount')->unsigned()->default(0);
                $table->smallInteger('parentid')->default(0);
                $table->smallInteger('displayorder')->default(0);
                $table->string('lastposter', 100)->default('');
                $table->integer('lastpost')->default(0);
            });
        }

        if (!Schema::hasTable('vb_threads')) {
            Schema::create('vb_threads', function (Blueprint $table) {
                $table->integer('threadid', true, true);
                $table->string('title', 250)->default('');
                $table->smallInteger('forumid')->unsigned()->default(0)->index();
                $table->integer('postuserid')->unsigned()->default(0)->index();
                $table->string('postusername', 100)->default('');
                $table->integer('dateline')->unsigned()->default(0)->index();
                $table->integer('lastpost')->unsigned()->default(0)->index();
                $table->string('lastposter', 100)->default('');
                $table->integer('replycount')->unsigned()->default(0);
                $table->integer('views')->unsigned()->default(0);
                $table->smallInteger('visible')->default(1);
                $table->smallInteger('open')->default(1);
            });
        }

        if (!Schema::hasTable('vb_posts')) {
            Schema::create('vb_posts', function (Blueprint $table) {
                $table->integer('postid', true, true);
                $table->integer('threadid')->unsigned()->default(0)->index();
                $table->integer('userid')->unsigned()->default(0)->index();
                $table->string('username', 100)->default('');
                $table->integer('dateline')->unsigned()->default(0);
                $table->mediumText('pagetext')->nullable();
                $table->smallInteger('visible')->default(1);
            });
        }

        if (!Schema::hasTable('vb_users')) {
            Schema::create('vb_users', function (Blueprint $table) {
                $table->integer('userid', true, true);
                $table->string('username', 100)->default('');
                $table->string('email', 100)->default('');
                $table->string('usertitle', 250)->default('');
                $table->integer('posts')->unsigned()->default(0);
            });
        }

        if (!Schema::hasTable('vb_custom_avatars')) {
            Schema::create('vb_custom_avatars', function (Blueprint $table) {
                $table->integer('userid')->unsigned()->primary();
                $table->string('filename', 100)->default('');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop in testing - these tables are preserved in production
        if (app()->environment('testing')) {
            Schema::dropIfExists('vb_custom_avatars');
            Schema::dropIfExists('vb_users');
            Schema::dropIfExists('vb_posts');
            Schema::dropIfExists('vb_threads');
            Schema::dropIfExists('vb_forums');
        }
    }
};
