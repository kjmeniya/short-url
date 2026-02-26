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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique role identifier (e.g., 'admin', 'user')
            $table->string('display_name'); // Human readable name (e.g., 'Administrator', 'Regular User')
            $table->text('description')->nullable(); // Role description
            $table->boolean('is_active')->default(true); // Whether role is active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
