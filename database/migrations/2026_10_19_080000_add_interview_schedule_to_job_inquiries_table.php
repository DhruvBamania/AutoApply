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
            $table->timestamp('shortlisted_at')->nullable()->after('status');
            $table->dateTime('interview_scheduled_at')->nullable()->after('shortlisted_at');
            $table->string('meeting_type')->nullable()->after('interview_scheduled_at'); // e.g. google_meet, zoom, in_person, phone
            $table->text('meeting_link_or_location')->nullable()->after('meeting_type');
            $table->text('interview_notes')->nullable()->after('meeting_link_or_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'shortlisted_at',
                'interview_scheduled_at',
                'meeting_type',
                'meeting_link_or_location',
                'interview_notes',
            ]);
        });
    }
};
