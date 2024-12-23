<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('usertitle')->default('Member');
            $table->string('avatar_path')->nullable();
            $table->integer('post_count')->default(0);
            $table->integer('posts_old')->default(0);
            $table->integer('pm_count')->default(0);
            $table->decimal('reputation', 8, 2)->default(0.00);
            $table->datetime('last_visit')->nullable();
            $table->datetime('last_activity')->nullable();
            $table->datetime('last_post')->nullable();
            $table->boolean('is_banned')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_moderator')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->datetime('legacy_join_date')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('token')->nullable();
            $table->string('theme', 50)->nullable();
            $table->string('timezone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
