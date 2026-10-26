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
            $table->text('linkedin_error')->nullable()->after('linkedin_posted_at');
            $table->text('naukri_error')->nullable()->after('naukri_posted_at');
            $table->text('indeed_error')->nullable()->after('indeed_posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['linkedin_error', 'naukri_error', 'indeed_error']);
        });
    }
};
