<?php

namespace App\Services;

use App\Models\User;
use App\Models\SocialPost;

class SocialPostGeneratorService
{
    protected GroqAiService $ai;

    public function __construct(GroqAiService $ai)
    {
        $this->ai = $ai;
    }

    public function generateForAllPlatforms(User $user)
    {
        $resumeText = $user->resume_text;

        if (!$resumeText) {
            throw new \Exception("Cannot generate posts without a parsed resume.");
        }

        $this->generateLinkedIn($user, $resumeText);
        $this->generateNaukri($user, $resumeText);
        $this->generateIndeed($user, $resumeText);
    }

    protected function generateLinkedIn(User $user, string $resumeText)
    {
        $prompt = "You are an expert career coach and personal branding specialist. 
Based on the following resume, write a highly engaging, professional LinkedIn post announcing that this person is looking for a new role. 
It should be formatted perfectly for LinkedIn (using line breaks and emojis sparingly).
Highlight their core skills, what they are looking for, and use 3-5 relevant hashtags.
Do NOT include any commentary, just the post itself.
Resume: " . $resumeText;

        $content = $this->ai->generate($prompt);

        if ($content) {
            SocialPost::updateOrCreate(
                ['user_id' => $user->id, 'platform' => 'linkedin'],
                ['content' => $content, 'status' => 'drafted']
            );
        }
    }

    protected function generateNaukri(User $user, string $resumeText)
    {
        $prompt = "Based on this resume, write a short, keyword-rich 'Profile Summary' suitable for Naukri.com.
It must be under 500 characters, highly professional, direct, and packed with the top technical and soft skills to attract recruiters.
Do NOT include hashtags or emojis.
Resume: " . $resumeText;

        $content = $this->ai->generate($prompt);

        if ($content) {
            SocialPost::updateOrCreate(
                ['user_id' => $user->id, 'platform' => 'naukri'],
                ['content' => $content, 'status' => 'drafted']
            );
        }
    }

    protected function generateIndeed(User $user, string $resumeText)
    {
        $prompt = "Based on this resume, write a clear, concise 'About Me' section for Indeed.
It should be professional, outline core competencies, years of experience, and immediate availability.
Do NOT include hashtags.
Resume: " . $resumeText;

        $content = $this->ai->generate($prompt);

        if ($content) {
            SocialPost::updateOrCreate(
                ['user_id' => $user->id, 'platform' => 'indeed'],
                ['content' => $content, 'status' => 'drafted']
            );
        }
    }
}
