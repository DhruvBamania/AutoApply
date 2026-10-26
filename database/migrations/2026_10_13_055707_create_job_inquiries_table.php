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
        Schema::create('job_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->string('platform')->default('direct'); // linkedin, naukri, indeed, direct
            $table->string('type')->default('query'); // comment, query, application
            $table->string('candidate_name');
            $table->string('candidate_email')->nullable();
            $table->string('candidate_headline')->nullable();
            $table->text('message');
            $table->string('resume_path')->nullable();
            $table->string('external_id')->nullable()->index(); // e.g. urn:li:comment:...
            $table->string('status')->default('new'); // new, under_review, replied, shortlisted, rejected
            $table->text('company_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_inquiries');
    }
};
