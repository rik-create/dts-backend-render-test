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
        Schema::create('user_refresh_tokens', function (Blueprint $table) {
            $table->id();
            // No DB-level FK constraint here — users table is created in a later migration batch (2026_03_03).
            // Cascade behavior is handled at the application level via model events.
            $table->unsignedBigInteger('user_id')->index();
            $table->string('refresh_token_hash');
            $table->string('selector');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_refresh_tokens');
    }
};
