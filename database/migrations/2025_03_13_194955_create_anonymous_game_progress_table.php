<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create anonymous game progress table
        Schema::create('anonymous_game_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puzzle_id')->constrained();
            $table->string('session_id')->index();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('solved')->default(false);
            $table->integer('attempts')->default(0);
            $table->integer('guesses_taken')->nullable();
            $table->json('previous_guesses')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Add unique constraint
            $table->unique(['puzzle_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anonymous_game_progress');
    }
};
