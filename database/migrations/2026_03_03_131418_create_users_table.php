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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_role_id')
            ->constrained('user_roles')
            ->restrictOnDelete()
            ->comment('Base role, finer permissions in user_group_members');
            // Link to the newly created offices table (acts like campus_id in hostel)
            $table->foreignId('office_id')->nullable()->constrained('offices')->comment('Mapped from dtsapp_profile.office_id');

            // Name Fields (Mapped from auth_user & dtsapp_profile)
            $table->string('first_name', 100)->index();
            $table->string('last_name', 100)->index();
            $table->string('middle_name', 100)->nullable()->comment('Mapped from dtsapp_profile');
            $table->string('display_name', 255)->nullable();

            // Login & Activity
            $table->string('email', 255)->unique()->index();
            $table->string('username', 150)->unique()->nullable()->comment('Mapped from auth_user for legacy login support if needed');
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable();

            // Contact info
            $table->string('contact_number', 100)->nullable()->comment('Mapped from dtsapp_profile');

            $table->boolean('has_admin_access')->default(false)->index()->comment('Mapped from Django is_staff. Grants basic entry to the admin area.');
            $table->boolean('is_superuser')->default(false)->index()->comment('Mapped from Django is_superuser. God-mode bypass for all system restrictions.');

            $table->boolean('is_active')->default(true)->index()->comment('Mapped from auth_user');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
