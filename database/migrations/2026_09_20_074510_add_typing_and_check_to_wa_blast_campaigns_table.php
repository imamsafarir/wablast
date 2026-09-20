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
        Schema::table('wa_blast_campaigns', function (Blueprint $table) {
            $table->boolean('enable_typing_simulation')->default(true)->after('enable_anti_report');
            $table->boolean('enable_number_check')->default(true)->after('enable_typing_simulation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_blast_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'enable_typing_simulation',
                'enable_number_check',
            ]);
        });
    }
};
