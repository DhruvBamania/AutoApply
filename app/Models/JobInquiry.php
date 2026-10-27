<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'platform',
        'type',
        'candidate_name',
        'candidate_email',
        'candidate_phone',
        'candidate_headline',
        'message',
        'resume_path',
        'external_id',
        'status',
        'company_reply',
        'replied_at',
        'is_pinned',
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
        'shortlisted_at',
        'interview_scheduled_at',
        'meeting_type',
        'meeting_link_or_location',
        'interview_notes',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'is_pinned' => 'boolean',
        'ai_match_score' => 'integer',
        'ai_rank' => 'integer',
        'ai_experience_score' => 'integer',
        'ai_skills_score' => 'integer',
        'ai_projects_score' => 'integer',
        'ai_matched_skills' => 'array',
        'ai_missing_skills' => 'array',
        'ai_analyzed_at' => 'datetime',
        'shortlisted_at' => 'datetime',
        'interview_scheduled_at' => 'datetime',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
}
