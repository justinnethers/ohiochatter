<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create puzzles table
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->date('publish_date')->unique();
            $table->string('answer');
            $table->integer('word_count');
            $table->string('image_path');
            $table->string('category')->nullable(); // person, place, thing, etc.
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->text('hint')->nullable();
            $table->timestamps();
        });

        // Create user game progress table
        Schema::create('user_game_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreignId('puzzle_id')->constrained();
            $table->boolean('solved')->default(false);
            $table->integer('attempts')->default(0);
            $table->integer('guesses_taken')->nullable();
            $table->json('previous_guesses')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Add foreign key with explicit reference to the users table id column
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Create user statistics table
        Schema::create('user_game_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->integer('games_played')->default(0);
            $table->integer('games_won')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('max_streak')->default(0);
            $table->json('guess_distribution')->nullable(); // Store as json: {"1": 5, "2": 10, ...}
            $table->date('last_played_date')->nullable();
            $table->timestamps();

            // Add foreign key with explicit reference to the users table id column
            $table->foreign('user_id')->references('id')->on('users');
            // Add unique constraint separately
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_game_stats');
        Schema::dropIfExists('user_game_progress');
        Schema::dropIfExists('puzzles');
    }
};
