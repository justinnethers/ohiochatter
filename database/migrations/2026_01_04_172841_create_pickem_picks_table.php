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
        Schema::create('pickem_picks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('pickem_matchup_id')->constrained()->onDelete('cascade');
            $table->enum('pick', ['a', 'b']);
            $table->unsignedInteger('confidence')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'pickem_matchup_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickem_picks');
    }
};
