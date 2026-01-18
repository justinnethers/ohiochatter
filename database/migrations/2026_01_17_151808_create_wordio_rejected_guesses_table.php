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
        Schema::create('wordio_rejected_guesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('guess', 20);
            $table->string('reason'); // not_in_dictionary, wrong_length, empty
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('word_id')->references('id')->on('wordle_words')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('guess');
            $table->index('reason');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wordio_rejected_guesses');
    }
};
