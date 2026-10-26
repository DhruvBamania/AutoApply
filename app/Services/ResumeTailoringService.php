<?php

namespace App\Services;

use App\Models\User;
use App\Models\TailoredResume;

class ResumeTailoringService
{
    protected GroqAiService $ai;

    public function __construct(GroqAiService $ai)
    {
        $this->ai = $ai;
    }

    public function generate(User $user, ?string $companyName, string $jobDescription): TailoredResume
    {
        $resumeText = $user->resume_text;

        if (!$resumeText) {
            throw new \Exception("Cannot tailor resume without an originally uploaded resume.");
        }

        $prompt = "You are an expert executive resume writer and ATS optimization specialist.\n"
            . "Please rewrite and tailor the provided resume to perfectly match the provided Job Description.\n"
            . "CRITICAL RULES FOR LAYOUT:\n"
            . "1. You MUST output the Name as Heading 1: `# [Full Name]`\n"
            . "2. Output contact info/subtitles as plain text directly below the name.\n"
            . "3. Use Heading 2 for sections: `## SUMMARY`, `## EXPERIENCE`, `## EDUCATION`, etc.\n"
            . "4. For every Job or Project entry, you MUST format the Title, Company, and Date on a SINGLE LINE separated by '||' like this:\n"
            . "`JOBTITLE||[Job Title] - [Company]||[Date]`\n"
            . "For example: `JOBTITLE||PHP Laravel Developer Intern - Excelsior Technologies||Feb 2026 - Jul 2026`\n"
            . "5. For every degree, format it similarly:\n"
            . "`EDUCATIONTITLE||[Degree] - [University]||[Date]`\n"
            . "6. Output bullet points starting with `- `.\n"
            . "Only edit the bullet points and summary to emphasize keywords from the Job Description. Do NOT add fake jobs.\n\n"
            . "=== JOB DESCRIPTION ===\n" . $jobDescription . "\n\n"
            . "=== ORIGINAL RESUME ===\n" . $resumeText;

        $content = $this->ai->generate($prompt);

        if (!$content) {
            throw new \Exception("Failed to generate tailored resume from AI.");
        }

        return TailoredResume::create([
            'user_id' => $user->id,
            'company_name' => $companyName,
            'job_description' => $jobDescription,
            'tailored_content' => $content,
        ]);
    }
}
