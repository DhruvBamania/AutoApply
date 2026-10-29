<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'resume_text',
        'resume_json',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function connectedAccounts()
    {
        return $this->hasMany(ConnectedAccount::class);
    }

    public function socialPosts()
    {
        return $this->hasMany(SocialPost::class);
    }

    public function tailoredResumes()
    {
        return $this->hasMany(TailoredResume::class);
    }

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

    public function jobInquiries()
    {
        return $this->hasManyThrough(JobInquiry::class, JobPosting::class);
    }

    public function interviewPreparations()
    {
        return $this->hasMany(InterviewPreparation::class);
    }

    public function mockInterviewSessions()
    {
        return $this->hasMany(MockInterviewSession::class)->latest('id');
    }

    public function headhunterInvitations()
    {
        return $this->hasMany(HeadhunterMatch::class, 'candidate_user_id')->latest('id');
    }

    public function contactMessages()
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function isCompany(): bool
    {
        return $this->role === 'company';
    }
}
