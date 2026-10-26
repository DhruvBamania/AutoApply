<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeadhunterMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'candidate_user_id',
        'candidate_name',
        'candidate_email',
        'candidate_headline',
        'candidate_location',
        'candidate_avatar_url',
        'source_platform',
        'external_profile_url',
        'summary_bio',
        'skills',
        'match_score',
        'match_reasons',
        'outreach_subject',
        'outreach_message',
        'status',
        'invited_at',
        'responded_at',
        'candidate_response_note',
        // Aliases for clean compatibility across services, views, and mobile
        'ai_match_score',
        'personalized_pitch',
        'pool_source',
        'current_title',
        'location',
        'ai_reasoning',
    ];

    protected $casts = [
        'skills' => 'array',
        'match_score' => 'integer',
        'match_reasons' => 'array',
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    protected $appends = [
        'ai_match_score',
        'personalized_pitch',
        'current_title',
        'location',
        'pool_source',
        'ai_reasoning',
    ];

    public function getAiMatchScoreAttribute(): int
    {
        return $this->attributes['match_score'] ?? 0;
    }

    public function setAiMatchScoreAttribute($value): void
    {
        $this->attributes['match_score'] = $value;
    }

    public function getPersonalizedPitchAttribute(): ?string
    {
        return $this->attributes['outreach_message'] ?? null;
    }

    public function setPersonalizedPitchAttribute($value): void
    {
        $this->attributes['outreach_message'] = $value;
    }

    public function getCurrentTitleAttribute(): ?string
    {
        return $this->attributes['candidate_headline'] ?? null;
    }

    public function getLocationAttribute(): ?string
    {
        return $this->attributes['candidate_location'] ?? null;
    }

    public function getPoolSourceAttribute(): string
    {
        $src = $this->attributes['source_platform'] ?? 'internal';
        return $src === 'internal' ? 'platform' : 'external';
    }

    public function getAiReasoningAttribute(): ?string
    {
        $reasons = $this->match_reasons;
        if (is_array($reasons)) {
            return implode(' • ', $reasons);
        }
        return is_string($reasons) ? $reasons : null;
    }

    public function setPoolSourceAttribute($value): void
    {
        $this->attributes['source_platform'] = $value === 'platform' ? 'internal' : ($value ?: 'internal');
    }

    public function setCurrentTitleAttribute($value): void
    {
        $this->attributes['candidate_headline'] = $value;
    }

    public function setLocationAttribute($value): void
    {
        $this->attributes['candidate_location'] = $value;
    }

    public function setAiReasoningAttribute($value): void
    {
        $this->attributes['match_reasons'] = is_array($value) 
            ? json_encode($value) 
            : (is_string($value) ? json_encode([$value]) : json_encode([]));
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function candidateUser()
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }
}
