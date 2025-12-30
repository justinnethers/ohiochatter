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
        Schema::create('guide_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedBigInteger('content_category_id')->nullable();
            $table->foreign('content_category_id')->references('id')->on('content_categories')->nullOnDelete();
            $table->unsignedBigInteger('content_type_id')->nullable();
            $table->foreign('content_type_id')->references('id')->on('content_types')->nullOnDelete();
            $table->nullableMorphs('locatable');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_drafts');
    }
};
