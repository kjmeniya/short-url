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
        Schema::create('laravel_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index(); // debug, info, notice, warning, error, critical, alert, emergency
            $table->string('channel', 50)->default('laravel')->index(); // laravel, single, daily, etc.
            $table->text('message'); // The log message
            $table->longText('context')->nullable(); // JSON context data
            $table->longText('extra')->nullable(); // JSON extra data
            $table->string('file_path')->nullable(); // Original log file path
            $table->integer('line_number')->nullable(); // Line number in the log file
            $table->string('environment', 20)->default('production')->index(); // local, staging, production
            $table->string('log_month', 7)->index(); // YYYY-MM format for month-wise organization
            $table->string('log_date', 10)->index(); // YYYY-MM-DD format for daily filtering
            $table->timestamp('logged_at')->index(); // When the log was originally created
            $table->string('exception_class')->nullable(); // Exception class name if applicable
            $table->text('stack_trace')->nullable(); // Stack trace for errors
            $table->string('request_id')->nullable()->index(); // Request ID for correlation
            $table->string('user_id')->nullable()->index(); // User ID if available
            $table->string('ip_address', 45)->nullable(); // IP address if available
            $table->string('user_agent')->nullable(); // User agent if available
            $table->string('url')->nullable(); // Request URL if available
            $table->string('method', 10)->nullable(); // HTTP method if available
            $table->json('metadata')->nullable(); // Additional metadata
            $table->boolean('is_processed')->default(false)->index(); // Whether log has been processed
            $table->timestamps();

            // Indexes for performance
            $table->index(['level', 'logged_at']);
            $table->index(['log_month', 'level']);
            $table->index(['environment', 'level']);
            $table->index(['logged_at', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laravel_logs');
    }
};
