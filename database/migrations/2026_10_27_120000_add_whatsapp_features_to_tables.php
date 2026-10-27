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
        // 1. Add WhatsApp contact number to Company Profiles
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('website');
            }
        });

        // 2. Add optional job-specific WhatsApp number override to Job Postings
        Schema::table('job_postings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_postings', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('status');
            }
        });

        // 3. Add Candidate Phone number to Inquiries table
        Schema::table('job_inquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('job_inquiries', 'candidate_phone')) {
                $table->string('candidate_phone')->nullable()->after('candidate_email');
            }
        });

        // 4. WhatsApp Messages logging & audit table
        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_posting_id')->nullable()->constrained('job_postings')->cascadeOnDelete();
                $table->foreignId('job_inquiry_id')->nullable()->constrained('job_inquiries')->nullOnDelete();
                $table->foreignId('headhunter_match_id')->nullable()->constrained('headhunter_matches')->nullOnDelete();
                $table->string('direction')->default('candidate_to_company'); // candidate_to_company, company_to_candidate
                $table->string('from_number');
                $table->string('to_number');
                $table->string('sender_name')->nullable();
                $table->string('sender_role')->default('candidate'); // candidate, company, system
                $table->text('message');
                $table->string('twilio_message_sid')->nullable();
                $table->string('status')->default('sent'); // sent, failed, simulated, delivered
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['job_posting_id', 'created_at']);
                $table->index('direction');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');

        Schema::table('job_inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('job_inquiries', 'candidate_phone')) {
                $table->dropColumn('candidate_phone');
            }
        });

        Schema::table('job_postings', function (Blueprint $table) {
            if (Schema::hasColumn('job_postings', 'whatsapp_number')) {
                $table->dropColumn('whatsapp_number');
            }
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('company_profiles', 'whatsapp_number')) {
                $table->dropColumn('whatsapp_number');
            }
        });
    }
};
