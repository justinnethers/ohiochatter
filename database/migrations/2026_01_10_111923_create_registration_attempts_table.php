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
        Schema::create('registration_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', [
                'success',
                'blocked_domain',
                'blocked_disposable',
                'blocked_ip_rate',
                'blocked_pattern',
                'blocked_stopforumspam',
                'blocked_honeypot',
            ])->index();
            $table->string('block_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_attempts');
    }
};
