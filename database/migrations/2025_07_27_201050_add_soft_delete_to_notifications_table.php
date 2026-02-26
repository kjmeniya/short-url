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
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('read_at');
            $table->string('deleted_by')->nullable()->after('deleted_at');

            // Add index for soft delete queries
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
