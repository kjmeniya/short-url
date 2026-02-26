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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable(); // Email message ID from mail provider
            $table->unsignedBigInteger('email_template_id')->nullable(); // Reference to email template used
            $table->unsignedBigInteger('user_id')->nullable(); // User who sent the email (for admin sent emails)
            $table->string('recipient_email'); // Email address of recipient
            $table->string('recipient_name')->nullable(); // Name of recipient
            $table->string('sender_email')->nullable(); // Email address of sender
            $table->string('sender_name')->nullable(); // Name of sender
            $table->string('subject'); // Email subject
            $table->longText('body')->nullable(); // Email body content
            $table->string('type')->default('general'); // Email type (general, password_reset, welcome, etc.)
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced', 'opened', 'clicked'])->default('pending');
            $table->json('metadata')->nullable(); // Additional data (variables used, etc.)
            $table->string('mailer')->nullable(); // Which mailer was used (smtp, ses, etc.)
            $table->timestamp('sent_at')->nullable(); // When email was actually sent
            $table->timestamp('delivered_at')->nullable(); // When email was delivered
            $table->timestamp('opened_at')->nullable(); // When email was opened (if tracking enabled)
            $table->timestamp('clicked_at')->nullable(); // When email links were clicked (if tracking enabled)
            $table->text('error_message')->nullable(); // Error message if failed
            $table->string('ip_address')->nullable(); // IP address of sender
            $table->string('user_agent')->nullable(); // User agent of sender
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['recipient_email', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['email_template_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
