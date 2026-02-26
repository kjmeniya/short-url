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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Contact Information
            $table->string('name', 100);
            $table->string('email', 255);
            $table->string('subject', 200);
            $table->text('message');

            // Status Management
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->boolean('is_spam')->default(false);

            // Reply Tracking
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reply_message')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes for better performance
            $table->index('email');
            $table->index('status');
            $table->index('is_spam');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
