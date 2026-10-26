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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'idx_jp_user_status_created');
            $table->index(['status', 'created_at'], 'idx_jp_status_created');
        });

        Schema::table('job_inquiries', function (Blueprint $table) {
            $table->index(['job_posting_id', 'is_pinned', 'created_at'], 'idx_ji_post_pin_created');
            $table->index(['job_posting_id', 'status'], 'idx_ji_post_status');
            $table->index(['job_posting_id', 'ai_rank'], 'idx_ji_post_ai_rank');
            $table->index('status', 'idx_ji_status');
            $table->index('platform', 'idx_ji_platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropIndex('idx_jp_user_status_created');
            $table->dropIndex('idx_jp_status_created');
        });

        Schema::table('job_inquiries', function (Blueprint $table) {
            $table->dropIndex('idx_ji_post_pin_created');
            $table->dropIndex('idx_ji_post_status');
            $table->dropIndex('idx_ji_post_ai_rank');
            $table->dropIndex('idx_ji_status');
            $table->dropIndex('idx_ji_platform');
        });
    }
};
