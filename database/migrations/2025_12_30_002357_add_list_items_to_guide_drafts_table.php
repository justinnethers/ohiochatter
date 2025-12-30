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
        Schema::table('guide_drafts', function (Blueprint $table) {
            $table->json('list_items')->nullable()->after('gallery');
            $table->json('list_settings')->nullable()->after('list_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_drafts', function (Blueprint $table) {
            $table->dropColumn(['list_items', 'list_settings']);
        });
    }
};
