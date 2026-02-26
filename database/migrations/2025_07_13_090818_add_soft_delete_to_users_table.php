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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');

            // Add foreign key constraint for deleted_by
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Add index for soft delete queries
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropIndex(['deleted_at']);
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
