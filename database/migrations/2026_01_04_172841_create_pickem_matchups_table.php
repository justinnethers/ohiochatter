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
        Schema::create('pickem_matchups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickem_id')->constrained()->onDelete('cascade');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('description')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('display_order')->default(0);
            $table->enum('winner', ['a', 'b', 'push'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickem_matchups');
    }
};
