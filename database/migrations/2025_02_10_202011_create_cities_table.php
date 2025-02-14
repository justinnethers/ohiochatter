<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('county_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('meta_title');
            $table->text('meta_description');
            $table->boolean('is_major')->default(false);
            $table->json('coordinates')->nullable(); // [latitude, longitude]
            $table->integer('population')->nullable();
            $table->json('demographics')->nullable();
            $table->year('incorporated_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
