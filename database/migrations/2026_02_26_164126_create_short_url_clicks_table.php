<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_url_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->constrained('short_urls')->cascadeOnDelete();

            // Network
            $table->string('ip_address', 45)->nullable()->index();

            // Device / Browser
            $table->string('browser', 100)->nullable()->index();
            $table->string('browser_version', 50)->nullable();
            $table->string('os', 100)->nullable()->index();
            $table->string('os_version', 50)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet', 'bot', 'unknown'])
                ->default('unknown')->index();
            $table->string('device_name', 100)->nullable();
            $table->string('user_agent')->nullable();

            // Geo
            $table->string('country', 100)->nullable()->index();
            $table->string('country_code', 10)->nullable();
            $table->string('city', 100)->nullable();

            // Source
            $table->string('referrer')->nullable()->index();
            $table->string('referrer_domain', 200)->nullable();

            $table->timestamp('clicked_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_url_clicks');
    }
};
