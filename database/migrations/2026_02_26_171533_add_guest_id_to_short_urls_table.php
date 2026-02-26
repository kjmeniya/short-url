<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            // Stores a UUID set in a browser cookie for anonymous/guest users
            $table->string('guest_id', 64)->nullable()->index()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->dropColumn('guest_id');
        });
    }
};
