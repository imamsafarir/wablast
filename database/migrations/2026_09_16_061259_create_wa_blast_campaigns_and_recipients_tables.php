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
        Schema::create('wa_blast_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul')->default('Blast');
            $table->text('pesan');
            $table->string('media_path')->nullable();
            $table->integer('total_target')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status')->default('pending')->index(); // pending, processing, completed, failed, cancelled
            $table->boolean('anti_bot')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wa_blast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_blast_campaign_id')->constrained('wa_blast_campaigns')->cascadeOnDelete();
            $table->string('nomor')->index();
            $table->string('nama')->nullable();
            $table->text('pesan_personal');
            $table->string('status')->default('pending')->index(); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_blast_recipients');
        Schema::dropIfExists('wa_blast_campaigns');
    }
};
