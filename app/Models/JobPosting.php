<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'job_type',
        'location',
        'salary_range',
        'experience_required',
        'skills',
        'description',
        'image_path',
        'status',
        'linkedin_status',
        'linkedin_post_id',
        'linkedin_posted_at',
        'linkedin_error',
        'naukri_status',
        'naukri_posted_at',
        'naukri_error',
        'indeed_status',
        'indeed_posted_at',
        'indeed_error',
        'last_ai_match_at',
        'last_headhunter_scout_at',
    ];

    protected $casts = [
        'linkedin_posted_at' => 'datetime',
        'naukri_posted_at' => 'datetime',
        'indeed_posted_at' => 'datetime',
        'last_ai_match_at' => 'datetime',
        'last_headhunter_scout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inquiries()
    {
        return $this->hasMany(JobInquiry::class)->orderBy('is_pinned', 'desc')->latest('created_at')->latest('id');
    }

    public function headhunterMatches()
    {
        return $this->hasMany(HeadhunterMatch::class)->orderBy('match_score', 'desc')->latest('id');
    }
}
