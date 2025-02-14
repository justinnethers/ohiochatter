<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('content')->onDelete('cascade');
            $table->foreignId('related_id')->constrained('content')->onDelete('cascade');
            $table->integer('weight')->default(0); // For ordering related content
            $table->timestamps();

            $table->unique(['content_id', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_content');
    }
};
