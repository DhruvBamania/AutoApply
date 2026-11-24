<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChromeExtensionService
{
    protected GeminiAiService $gemini;
    protected GroqAiService $groq;

    public function __construct(GeminiAiService $gemini, GroqAiService $groq)
    {
        $this->gemini = $gemini;
        $this->groq = $groq;
    }

    /**
     * Analyze a job description against candidate resume.
     */
    public function analyzeJob(User $user, array $jobData): array
    {
        $jobTitle = $jobData['job_title'] ?? 'Role';
        $company = $jobData['company_name'] ?? 'Company';
        $jobDescription = $jobData['job_description'] ?? '';
        $resumeText = $user->resume_text ?: 'Full Stack Developer with experience in web applications, databases, REST APIs, and modern frontend/backend frameworks.';

        $prompt = <<<PROMPT
You are an expert ATS (Applicant Tracking System) Auditor and Career Intelligence Agent for AutoApply.
Analyze the following Job Description against the Candidate's Resume Profile.

TARGET ROLE: {$jobTitle} at {$company}
JOB DESCRIPTION:
{$jobDescription}

CANDIDATE RESUME:
{$resumeText}

Analyze and return ONLY valid JSON matching this exact structure with NO markdown or extra text:
{
  "match_score": 85,
  "summary": "Two concise sentences summarizing the candidate's alignment with this role.",
  "matching_skills": ["Skill 1", "Skill 2", "Skill 3"],
  "missing_skills": ["Skill A", "Skill B"],
  "strengths": ["Key strength 1 matching core requirement", "Key strength 2"],
  "recommendation": "Strong Fit (Recommended to Apply)",
  "ats_tips": ["Actionable tip 1 to optimize application", "Actionable tip 2"]
}
PROMPT;

        $jsonResponse = null;

        // 1. Try Gemini AI
        if ($this->gemini->isConfigured()) {
            try {
                $raw = $this->gemini->generate($prompt, true);
                if ($raw) {
                    $jsonResponse = json_decode($this->cleanJson($raw), true);
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini extension job analysis failed: ' . $e->getMessage());
            }
        }

        // 2. Try Groq AI Fallback
        if (!$jsonResponse && $this->groq->isConfigured()) {
            try {
                $raw = $this->groq->generateCompletion($prompt, 0.2);
                if ($raw) {
                    $jsonResponse = json_decode($this->cleanJson($raw), true);
                }
            } catch (\Throwable $e) {
                Log::warning('Groq extension job analysis failed: ' . $e->getMessage());
            }
        }

        // 3. Fallback Heuristic Matcher
        if (!$jsonResponse || !isset($jsonResponse['match_score'])) {
            $jsonResponse = $this->heuristicJobAnalysis($resumeText, $jobDescription, $jobTitle, $company);
        }

        // Normalize response keys across AI and heuristic engines
        $jsonResponse['job_title'] = $jobTitle;
        $jsonResponse['company'] = $company;
        $jsonResponse['missing_keywords'] = $jsonResponse['missing_skills'] ?? [];
        $jsonResponse['matched_keywords'] = $jsonResponse['matching_skills'] ?? [];
        $jsonResponse['extracted_keywords'] = array_values(array_unique(array_merge($jsonResponse['matched_keywords'], $jsonResponse['missing_keywords'])));
        $jsonResponse['explanation'] = $jsonResponse['summary'] ?? "Demonstrated alignment for {$jobTitle}.";
        $jsonResponse['recommendations'] = is_array($jsonResponse['recommendation'] ?? null)
            ? $jsonResponse['recommendation']
            : [$jsonResponse['recommendation'] ?? 'Tailor keywords to match target role.'];

        return $jsonResponse;
    }

    /**
     * Generate customized answer to screening question.
     */
    public function generateScreeningAnswer(User $user, array $data): array
    {
        $question = $data['question'] ?? 'Why are you a good fit for this role?';
        $jobTitle = $data['job_title'] ?? 'the position';
        $company = $data['company_name'] ?? 'the company';
        $jobDescription = $data['job_description'] ?? '';
        $tone = $data['tone'] ?? 'professional';
        $resumeText = $user->resume_text ?: 'Experienced software professional with demonstrated track record.';

        $prompt = <<<PROMPT
You are a senior career advisor crafting a high-impact, authentic response for an online job application form (such as on LinkedIn, Lever, Greenhouse, or Workday).

JOB ROLE: {$jobTitle} at {$company}
SCREENING QUESTION: {$question}
JOB CONTEXT:
{$jobDescription}

CANDIDATE BACKGROUND:
{$resumeText}

TONE: {$tone}

Write a compelling, professional answer (100 to 180 words) that directly addresses the question using specific skills and achievements from the candidate's profile.
Return ONLY valid JSON with this exact structure:
{
  "answer": "The complete, polished response ready to paste into the application form.",
  "key_highlights": ["Key point 1 addressed", "Key point 2 addressed"]
}
PROMPT;

        $jsonResponse = null;

        if ($this->gemini->isConfigured()) {
            try {
                $raw = $this->gemini->generate($prompt, true);
                if ($raw) {
                    $jsonResponse = json_decode($this->cleanJson($raw), true);
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini extension answer generation failed: ' . $e->getMessage());
            }
        }

        if (!$jsonResponse && $this->groq->isConfigured()) {
            try {
                $raw = $this->groq->generateCompletion($prompt, 0.4);
                if ($raw) {
                    $jsonResponse = json_decode($this->cleanJson($raw), true);
                }
            } catch (\Throwable $e) {
                Log::warning('Groq extension answer generation failed: ' . $e->getMessage());
            }
        }

        if (!$jsonResponse || empty($jsonResponse['answer'])) {
            $candidateName = $user->name ?: 'Candidate';
            $jsonResponse = [
                'answer' => "With my background in software engineering and hands-on experience building scalable applications, I am very excited about the {$jobTitle} role at {$company}. My technical skill set aligns directly with your requirements, particularly in delivering clean, maintainable solutions and collaborating across teams. I am eager to contribute my problem-solving skills and enthusiasm to help {$company} achieve its technical and product milestones.",
                'key_highlights' => [
                    'Demonstrated technical experience matching job criteria',
                    'Proven problem solving and team collaboration skills'
                ]
            ];
        }

        return $jsonResponse;
    }

    /**
     * Get structured candidate autofill profile map.
     */
    public function getAutofillProfile(User $user): array
    {
        $resumeJson = $user->resume_json ? json_decode($user->resume_json, true) : [];
        $parts = explode(' ', trim($user->name ?: ''));
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        // Extract skills
        $skills = $resumeJson['skills'] ?? ['PHP', 'Laravel', 'JavaScript', 'MySQL', 'REST APIs', 'Git'];
        $skillsString = is_array($skills) ? implode(', ', $skills) : (string)$skills;

        // Extract experience
        $experience = $resumeJson['experience'] ?? [];
        $currentExp = $experience[0] ?? [];
        $currentRole = $currentExp['title'] ?? 'Software Developer';
        $currentCompany = $currentExp['company'] ?? 'Tech Solutions';

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $user->name ?: 'Candidate',
            'email' => $user->email,
            'phone' => $resumeJson['contact'] ?? '+1 555-0199',
            'location' => $resumeJson['location'] ?? 'Remote / Open to Relocation',
            'city' => $resumeJson['city'] ?? 'Metropolitan Area',
            'state' => $resumeJson['state'] ?? '',
            'country' => 'United States',
            'linkedin_url' => "https://linkedin.com/in/" . strtolower(str_replace(' ', '', $user->name ?: 'candidate')),
            'github_url' => "https://github.com/" . strtolower(str_replace(' ', '', $user->name ?: 'candidate')),
            'portfolio_url' => url('/'),
            'headline' => $currentRole . ' | AI & Software Engineering',
            'current_title' => $currentRole,
            'current_company' => $currentCompany,
            'years_of_experience' => '3+',
            'skills' => is_array($skills) ? $skills : explode(',', $skillsString),
            'skills_string' => $skillsString,
            'summary' => $resumeJson['summary'] ?? ($user->name . ' is an experienced software engineer specialized in building resilient, modern web platforms and APIs.'),
        ];
    }

    /**
     * Heuristic analysis fallback when AI API is unavailable.
     */
    protected function heuristicJobAnalysis(string $resumeText, string $jobDescription, string $jobTitle, string $company): array
    {
        $knownSkills = [
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'Vue', 'React', 'Angular',
            'Node.js', 'Python', 'Django', 'Flask', 'MySQL', 'PostgreSQL', 'MongoDB',
            'Redis', 'Docker', 'Kubernetes', 'AWS', 'GCP', 'Azure', 'Git', 'CI/CD',
            'REST API', 'GraphQL', 'Tailwind', 'Bootstrap', 'HTML5', 'CSS3', 'Agile'
        ];

        $matched = [];
        $missing = [];

        $resumeLower = strtolower($resumeText);
        $jdLower = strtolower($jobDescription);

        foreach ($knownSkills as $skill) {
            $pattern = '/\b' . preg_quote(strtolower($skill), '/') . '\b/';
            $inJd = preg_match($pattern, $jdLower);
            $inResume = preg_match($pattern, $resumeLower);

            if ($inJd && $inResume) {
                $matched[] = $skill;
            } elseif ($inJd && !$inResume) {
                $missing[] = $skill;
            }
        }

        if (empty($matched)) {
            $matched = ['PHP', 'REST APIs', 'Git', 'Software Development'];
        }
        if (empty($missing)) {
            $missing = ['Cloud Infrastructure', 'Containerization'];
        }

        $total = count($matched) + count($missing);
        $score = $total > 0 ? (int)round((count($matched) / $total) * 100) : 80;
        $score = max(65, min(95, $score));

        $recommendation = $score >= 80 ? 'Strong Fit (Recommended to Apply)' : ($score >= 70 ? 'Good Match (Highlight Overlapping Skills)' : 'Moderate Match (Tailor Resume First)');

        return [
            'match_score' => $score,
            'summary' => "Demonstrates strong foundational alignment for {$jobTitle} at {$company}. Key technical proficiencies match core expectations.",
            'matching_skills' => array_values(array_unique($matched)),
            'missing_skills' => array_values(array_unique($missing)),
            'strengths' => [
                'Demonstrated core proficiency in primary tech stack requirements',
                'Strong background in web architecture and full-lifecycle development'
            ],
            'recommendation' => $recommendation,
            'ats_tips' => [
                'Ensure terms from missing skills are incorporated into your experience bullets where applicable',
                'Mirror exact technical keywords found in the job description to maximize ATS pass rate'
            ]
        ];
    }

    /**
     * Clean markdown and code fences from AI json responses.
     */
    protected function cleanJson(string $raw): string
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        return trim($clean);
    }
}

