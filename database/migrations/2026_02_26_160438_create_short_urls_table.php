<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->index();
            $table->text('original_url');
            $table->string('title')->nullable();
            $table->string('custom_alias', 100)->nullable()->unique();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
