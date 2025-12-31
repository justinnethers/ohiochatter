<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guide_drafts', function (Blueprint $table) {
            $table->json('category_ids')->nullable()->after('content_category_id');
        });

        // Migrate existing single category to array format
        DB::table('guide_drafts')
            ->whereNotNull('content_category_id')
            ->orderBy('id')
            ->each(function ($draft) {
                DB::table('guide_drafts')
                    ->where('id', $draft->id)
                    ->update(['category_ids' => json_encode([$draft->content_category_id])]);
            });
    }

    public function down(): void
    {
        Schema::table('guide_drafts', function (Blueprint $table) {
            $table->dropColumn('category_ids');
        });
    }
};
