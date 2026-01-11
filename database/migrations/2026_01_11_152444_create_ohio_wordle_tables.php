<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Words table - stores the daily puzzles
        Schema::create('wordle_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->unsignedTinyInteger('word_length')->index();
            $table->string('category')->nullable(); // city, landmark, person, misc
            $table->text('hint')->nullable();
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->date('publish_date')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['publish_date', 'is_active']);
        });

        // User progress table - tracks authenticated user game state
        Schema::create('wordle_user_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('word_id');
            $table->boolean('solved')->default(false);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('guesses_taken')->nullable();
            $table->json('guesses')->nullable();
            $table->json('feedback')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'word_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('word_id')->references('id')->on('wordle_words')->onDelete('cascade');
        });

        // Anonymous progress table - tracks guest game state
        Schema::create('wordle_anonymous_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_id');
            $table->string('session_id')->index();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('solved')->default(false);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('guesses_taken')->nullable();
            $table->json('guesses')->nullable();
            $table->json('feedback')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['word_id', 'session_id']);
            $table->foreign('word_id')->references('id')->on('wordle_words')->onDelete('cascade');
        });

        // User stats table - tracks overall statistics and streaks
        Schema::create('wordle_user_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->unique();
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('max_streak')->default(0);
            $table->json('guess_distribution')->nullable();
            $table->date('last_played_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wordle_user_stats');
        Schema::dropIfExists('wordle_anonymous_progress');
        Schema::dropIfExists('wordle_user_progress');
        Schema::dropIfExists('wordle_words');
    }
};
