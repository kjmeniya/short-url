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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->uuid('guest_id')->nullable()->index();
            $table->string('page')->index();
            $table->string('platform')->index(); // web, admin, app
            $table->string('device')->index(); // desktop, mobile, tablet
            $table->string('ip', 45)->nullable();
            $table->timestamp('visited_at')->index();
            $table->timestamps();

            // Index for uniqueness check performance
            $table->index(['user_id', 'page', 'visited_at']);
            $table->index(['guest_id', 'page', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
