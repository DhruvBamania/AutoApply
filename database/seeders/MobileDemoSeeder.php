<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MobileDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Company Demo User
        $companyUser = User::firstOrCreate(
            ['email' => 'employer@demo.com'],
            [
                'name' => 'TechCorp HR',
                'password' => Hash::make('password123'),
                'role' => 'company',
            ]
        );

        CompanyProfile::firstOrCreate(
            ['user_id' => $companyUser->id],
            [
                'company_name' => 'TechCorp Global Solutions',
                'website' => 'https://techcorp.example.com',
                'location' => 'Bangalore, India (Hybrid)',
                'description' => 'Global technology leader building modern AI and mobile products.',
            ]
        );

        // Ensure at least one active job posting exists for this company
        $job = JobPosting::firstOrCreate(
            [
                'user_id' => $companyUser->id,
                'title' => 'Senior Full Stack & AI Engineer',
            ],
            [
                'job_type' => 'Full-time',
                'location' => 'Bangalore / Remote',
                'salary_range' => '₹18,00,000 - ₹28,00,000',
                'experience_required' => '3 - 6 Years',
                'skills' => 'Flutter, Laravel, PHP, Dart, Gemini AI, MySQL',
                'description' => 'We are seeking an experienced Full Stack & Mobile Engineer to spearhead cross-platform recruitment applications using Flutter, Laravel REST APIs, and Gemini AI integrations.',
                'status' => 'active',
                'linkedin_status' => 'published',
                'naukri_status' => 'published',
                'indeed_status' => 'published',
            ]
        );

        // Ensure sample inquiries exist for this job
        JobInquiry::firstOrCreate(
            [
                'job_posting_id' => $job->id,
                'candidate_email' => 'aarav.sharma@example.com',
            ],
            [
                'candidate_name' => 'Aarav Sharma',
                'candidate_headline' => 'Senior Mobile & AI Architect | 5+ YOE',
                'platform' => 'linkedin',
                'type' => 'query',
                'message' => 'Hi HR Team, is this position open to 100% remote work for candidates outside Bangalore? I have extensive experience with Flutter and Laravel.',
                'status' => 'new',
                'is_pinned' => true,
                'ai_match_score' => 94.5,
                'ai_rank' => 1,
                'ai_experience_score' => 95.0,
                'ai_skills_score' => 96.0,
                'ai_projects_score' => 92.0,
                'ai_recommendation' => 'Strong Match',
                'ai_summary' => 'Candidate exhibits exceptional experience across the entire required tech stack with verified Flutter and Laravel production SaaS projects.',
                'ai_matched_skills' => ['Flutter', 'Dart', 'Laravel', 'REST APIs', 'Gemini AI'],
                'ai_missing_skills' => [],
            ]
        );

        JobInquiry::firstOrCreate(
            [
                'job_posting_id' => $job->id,
                'candidate_email' => 'priya.patel@example.com',
            ],
            [
                'candidate_name' => 'Priya Patel',
                'candidate_headline' => 'Full Stack Developer | React & PHP',
                'platform' => 'naukri',
                'type' => 'application',
                'message' => 'Applying for the Full Stack & AI role. I have 4 years building scalable web and mobile solutions.',
                'status' => 'reviewed',
                'is_pinned' => false,
                'ai_match_score' => 82.0,
                'ai_rank' => 2,
                'ai_experience_score' => 85.0,
                'ai_skills_score' => 80.0,
                'ai_projects_score' => 81.0,
                'ai_recommendation' => 'Potential Match',
                'ai_summary' => 'Strong backend foundations in PHP and Laravel; moderate Flutter mobile experience.',
                'ai_matched_skills' => ['Laravel', 'PHP', 'MySQL', 'REST APIs'],
                'ai_missing_skills' => ['Gemini AI'],
            ]
        );

        // 2. Candidate Demo User
        User::firstOrCreate(
            ['email' => 'candidate@demo.com'],
            [
                'name' => 'Demo Candidate',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );
    }
}
