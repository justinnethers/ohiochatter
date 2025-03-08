<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('puzzles', function (Blueprint $table) {
            $table->string('image_attribution')->nullable();
            $table->string('link')->nullable();
            $table->string('hint_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puzzles', function (Blueprint $table) {
            $table->dropColumn('image_attribution');
            $table->dropColumn('link');
            $table->dropColumn('hint_2');
        });
    }
};
