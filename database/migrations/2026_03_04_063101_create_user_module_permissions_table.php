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
        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            
            // Nakaturo direct kay User (e.g., Kay Admin 1)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Nakaturo sa specific na action (e.g., "Create" sa "User Management")
            $table->foreignId('module_permission_id')->constrained('module_permissions')->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
    }
};