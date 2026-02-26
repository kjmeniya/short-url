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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, select, textarea, email, url, file, color, password
            $table->json('options')->nullable(); // For select type options
            $table->string('group')->default('general'); // general, email, smtp, storage, security, appearance, system
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes from the start

            $table->index(['is_active']);
            $table->index(['is_public']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
