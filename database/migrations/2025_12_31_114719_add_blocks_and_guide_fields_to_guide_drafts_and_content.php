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
            $table->json('blocks')->nullable()->after('list_settings');
            $table->tinyInteger('rating')->nullable()->after('blocks');
            $table->string('website', 255)->nullable()->after('rating');
            $table->string('address', 500)->nullable()->after('website');
        });

        Schema::table('content', function (Blueprint $table) {
            $table->json('blocks')->nullable()->after('metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_drafts', function (Blueprint $table) {
            $table->dropColumn(['blocks', 'rating', 'website', 'address']);
        });

        Schema::table('content', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }
};
