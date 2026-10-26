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
        Schema::table('job_inquiries', function (Blueprint $table) {
            $table->unsignedInteger('ai_match_score')->nullable()->after('status')->index();
            $table->unsignedInteger('ai_rank')->nullable()->after('ai_match_score')->index();
            $table->unsignedInteger('ai_experience_score')->nullable()->after('ai_rank');
            $table->unsignedInteger('ai_skills_score')->nullable()->after('ai_experience_score');
            $table->unsignedInteger('ai_projects_score')->nullable()->after('ai_skills_score');
            $table->string('ai_recommendation')->nullable()->after('ai_projects_score');
            $table->text('ai_summary')->nullable()->after('ai_recommendation');
            $table->json('ai_matched_skills')->nullable()->after('ai_summary');
            $table->json('ai_missing_skills')->nullable()->after('ai_matched_skills');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_missing_skills');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->timestamp('last_ai_match_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'ai_match_score',
                'ai_rank',
                'ai_experience_score',
                'ai_skills_score',
                'ai_projects_score',
                'ai_recommendation',
                'ai_summary',
                'ai_matched_skills',
                'ai_missing_skills',
                'ai_analyzed_at',
            ]);
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('last_ai_match_at');
        });
    }
};
