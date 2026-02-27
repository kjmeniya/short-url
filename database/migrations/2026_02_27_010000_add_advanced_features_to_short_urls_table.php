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
        Schema::table('short_urls', function (Blueprint $table) {
            // General settings
            $table->unsignedInteger('max_clicks')->nullable()->after('clicks');
            $table->unsignedTinyInteger('redirect_delay')->default(0)->after('max_clicks');
            $table->boolean('is_private')->default(false)->after('password');
            $table->boolean('is_24h_story')->default(false)->after('is_private');
            $table->boolean('is_one_time')->default(false)->after('is_24h_story');
            $table->timestamp('disabled_at')->nullable()->after('is_one_time');

            // Device targeting
            $table->string('mobile_url', 2048)->nullable()->after('original_url');
            $table->string('desktop_url', 2048)->nullable()->after('mobile_url');
            $table->string('tablet_url', 2048)->nullable()->after('desktop_url');

            // Office Hours / Time-based redirects
            $table->string('timezone')->nullable()->after('tablet_url');
            $table->json('office_days')->nullable()->after('timezone');
            $table->time('office_start_time')->nullable()->after('office_days');
            $table->time('office_end_time')->nullable()->after('office_start_time');
            $table->string('office_url', 2048)->nullable()->after('office_end_time');
            $table->string('after_hours_url', 2048)->nullable()->after('office_url');

            // Custom Social Preview (Open Graph)
            $table->string('og_title')->nullable()->after('after_hours_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image', 2048)->nullable()->after('og_description');
        });

        // IP Blocking Table
        Schema::create('ip_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['ip', 'cidr'])->default('ip');
            $table->string('value');
            $table->timestamps();
            
            $table->index(['short_url_id', 'type', 'value']);
        });

        // Blocked attempts log
        Schema::create('blocked_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('reason')->nullable();
            $table->json('visitor_data')->nullable(); // Browser, OS, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_logs');
        Schema::dropIfExists('ip_blocks');

        Schema::table('short_urls', function (Blueprint $table) {
            $table->dropColumn([
                'max_clicks',
                'redirect_delay',
                'is_private',
                'is_24h_story',
                'is_one_time',
                'disabled_at',
                'mobile_url',
                'desktop_url',
                'tablet_url',
                'timezone',
                'office_days',
                'office_start_time',
                'office_end_time',
                'office_url',
                'after_hours_url',
                'og_title',
                'og_description',
                'og_image',
            ]);
        });
    }
};
