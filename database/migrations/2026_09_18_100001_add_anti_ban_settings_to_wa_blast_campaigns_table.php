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
            $table->string('speed_mode')->default('super_safe')->after('anti_bot');
            $table->unsignedInteger('delay_min')->default(30)->after('speed_mode');
            $table->unsignedInteger('delay_max')->default(60)->after('delay_min');
            $table->unsignedInteger('batch_size')->default(20)->after('delay_max');
            $table->unsignedInteger('batch_cooldown')->default(180)->after('batch_size');
            $table->boolean('enable_spintax')->default(true)->after('batch_cooldown');
            $table->boolean('enable_zero_width_hash')->default(true)->after('enable_spintax');
            $table->boolean('enable_anti_report')->default(false)->after('enable_zero_width_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_blast_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'speed_mode',
                'delay_min',
                'delay_max',
                'batch_size',
                'batch_cooldown',
                'enable_spintax',
                'enable_zero_width_hash',
                'enable_anti_report',
            ]);
        });
    }
};
