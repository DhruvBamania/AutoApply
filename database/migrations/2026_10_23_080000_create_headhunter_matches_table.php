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
        Schema::create('headhunter_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('candidate_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->string('candidate_headline')->nullable();
            $table->string('candidate_location')->nullable();
            $table->string('candidate_avatar_url')->nullable();
            $table->string('source_platform')->default('internal'); // internal, github, linkedin, stackoverflow
            $table->string('external_profile_url')->nullable();
            $table->text('summary_bio')->nullable();
            $table->json('skills')->nullable();
            $table->unsignedTinyInteger('match_score')->default(0); // 0-100
            $table->json('match_reasons')->nullable(); // list of bullet points
            $table->string('outreach_subject')->nullable();
            $table->text('outreach_message')->nullable();
            $table->string('status')->default('sourced'); // sourced, invited, accepted, declined
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('candidate_response_note')->nullable();
            $table->timestamps();

            $table->index(['job_posting_id', 'status']);
            $table->index('candidate_email');
            $table->index('candidate_user_id');
            $table->index('match_score');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->timestamp('last_headhunter_scout_at')->nullable()->after('last_ai_match_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('last_headhunter_scout_at');
        });

        Schema::dropIfExists('headhunter_matches');
    }
};
