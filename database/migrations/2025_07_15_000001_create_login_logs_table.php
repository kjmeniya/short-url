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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // User who logged in
            $table->string('email'); // Email used for login attempt
            $table->string('name')->nullable(); // User name at time of login
            $table->string('ip_address'); // IP address of login attempt
            $table->string('user_agent')->nullable(); // Browser/device information
            $table->string('device_type')->nullable(); // Mobile, Desktop, Tablet
            $table->string('browser')->nullable(); // Chrome, Firefox, Safari, etc.
            $table->string('platform')->nullable(); // Windows, macOS, Linux, etc.
            $table->enum('status', ['success', 'failed', 'blocked', 'locked'])->default('failed');
            $table->enum('type', ['login', 'logout', 'password_reset', 'account_locked'])->default('login');
            $table->string('location')->nullable(); // Geographic location if available
            $table->string('country')->nullable(); // Country code
            $table->string('city')->nullable(); // City name
            $table->text('failure_reason')->nullable(); // Reason for failed login
            $table->json('metadata')->nullable(); // Additional data (session info, etc.)
            $table->timestamp('login_at')->nullable(); // When login occurred
            $table->timestamp('logout_at')->nullable(); // When logout occurred (if applicable)
            $table->integer('session_duration')->nullable(); // Session duration in minutes
            $table->boolean('is_suspicious')->default(false); // Flag for suspicious activity
            $table->string('session_id')->nullable(); // Laravel session ID
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
            $table->index('login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
