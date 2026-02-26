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
            // Only add columns that don't exist
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin', 'super_admin'])->default('user')->after('email_verified_at');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }

            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('profile_image');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('permissions');
            }

            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('last_login_at');
            }

            if (!Schema::hasColumn('users', 'login_attempts')) {
                $table->integer('login_attempts')->default(0)->after('last_login_ip');
            }

            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('login_attempts');
            }

            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('UTC')->after('locked_until');
            }

            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 5)->default('en')->after('timezone');
            }

            if (!Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('language');
            }

            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('preferences');
            }

            if (!Schema::hasColumn('users', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false)->after('password_changed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'role', 'is_active', 'profile_image', 'permissions', 'last_login_at',
                'last_login_ip', 'login_attempts', 'locked_until', 'timezone',
                'language', 'preferences', 'password_changed_at', 'force_password_change'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
