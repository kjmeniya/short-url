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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique permission identifier (e.g., 'users.create')
            $table->string('display_name'); // Human readable name (e.g., 'Create Users')
            $table->text('description')->nullable(); // Permission description
            $table->string('route_name')->nullable(); // Laravel route name
            $table->string('method')->default('GET'); // HTTP method (GET, POST, PUT, DELETE, etc.)
            $table->string('category')->default('general'); // Permission category for grouping
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
