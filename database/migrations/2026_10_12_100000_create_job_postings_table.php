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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('job_type')->default('Full-time');
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('experience_required')->nullable();
            $table->text('skills')->nullable();
            $table->longText('description');
            $table->string('status')->default('active'); // active, closed, draft

            // Platform 1-Click distribution statuses
            $table->string('linkedin_status')->default('pending'); // not_selected, pending, posted, failed
            $table->timestamp('linkedin_posted_at')->nullable();

            $table->string('naukri_status')->default('pending');
            $table->timestamp('naukri_posted_at')->nullable();

            $table->string('indeed_status')->default('pending');
            $table->timestamp('indeed_posted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
