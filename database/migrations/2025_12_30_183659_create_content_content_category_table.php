<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_content_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('content')->onDelete('cascade');
            $table->foreignId('content_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['content_id', 'content_category_id']);
        });

        // Migrate existing single-category relationships to pivot table
        DB::table('content')
            ->whereNotNull('content_category_id')
            ->orderBy('id')
            ->each(function ($content) {
                DB::table('content_content_category')->insert([
                    'content_id' => $content->id,
                    'content_category_id' => $content->content_category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_content_category');
    }
};
