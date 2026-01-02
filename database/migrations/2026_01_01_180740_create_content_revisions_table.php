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
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('content')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Revision content (mirrors Content fields)
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->json('blocks')->nullable();
            $table->json('metadata')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('category_ids')->nullable();

            // Workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();
            $table->index(['content_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};
