<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('role')->default('pengguna')->after('email')->index();
        });

        // Update existing users
        DB::table('users')->where('email', 'admin@admin.com')->update([
            'username' => 'admin',
            'role' => 'superadmin',
        ]);

        DB::table('users')->whereNull('username')->update([
            'username' => DB::raw("CONCAT('user_', id)"),
            'role' => 'superadmin',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role']);
        });
    }
};
