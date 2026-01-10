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
        Schema::create('blocked_email_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique()->index();
            $table->string('reason')->nullable();
            $table->enum('type', ['manual', 'disposable', 'stopforumspam'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_email_domains');
    }
};
