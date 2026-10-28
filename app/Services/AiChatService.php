<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected GroqAiService $groqService;

    public function __construct(GroqAiService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Generate an AI response to a user query within the site context.
     *
     * @param string $userMessage
     * @param array $history
     * @param User|null $user
     * @return array{reply: string, jobs: array}
     */
    public function generateReply(string $userMessage, array $history = [], ?User $user = null): array
    {
        // 1. Search relevant active jobs from the database
        $matchedJobs = $this->searchActiveJobs($userMessage);

        // 2. Build system instructions with real-time website context
        $systemPrompt = $this->buildSystemPrompt($user, $matchedJobs);

        // 3. Assemble message payload for Groq
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Sanitize and append previous conversational turns (keep up to last 8 turns)
        $cleanHistory = array_slice($history, -8);
        foreach ($cleanHistory as $turn) {
            if (isset($turn['role'], $turn['content']) && in_array($turn['role'], ['user', 'assistant'], true)) {
                $messages[] = [
                    'role' => $turn['role'],
                    'content' => mb_substr(trim($turn['content']), 0, 1500),
                ];
            }
        }

        // Append current user message
        $messages[] = [
            'role' => 'user',
            'content' => mb_substr(trim($userMessage), 0, 2000),
        ];

        // 4. Request completion from Groq AI
        $rawReply = $this->groqService->chat($messages);

        if (!$rawReply) {
            Log::warning('AiChatService: Groq returned empty response, using fallback response.');
            return [
                'reply' => $this->getFallbackReply($userMessage, $matchedJobs),
                'jobs' => $matchedJobs->map(fn($job) => $this->formatJobPayload($job))->values()->toArray(),
            ];
        }

        return [
            'reply' => trim($rawReply),
            'jobs' => $matchedJobs->map(fn($job) => $this->formatJobPayload($job))->values()->toArray(),
        ];
    }

    /**
     * Search active jobs relevant to the query or return recent openings.
     */
    protected function searchActiveJobs(string $query): Collection
    {
        $rawWords = preg_split('/[\s,\.\?\!\-\/]+/', mb_strtolower($query));
        $keywords = [];

        foreach ($rawWords as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 3 && !in_array($w, ['the', 'and', 'for', 'with', 'about', 'show', 'tell', 'what', 'where', 'some', 'any', 'are', 'find'])) {
                $keywords[] = $w;
                // Add singular if plural (e.g. roles -> role, jobs -> job, developers -> developer)
                if (str_ends_with($w, 's') && mb_strlen($w) > 3) {
                    $keywords[] = rtrim($w, 's');
                }
            }
        }
        $keywords = array_unique($keywords);

        $queryBuilder = JobPosting::query()
            ->where('status', 'active')
            ->with(['user.companyProfile']);

        if (!empty($keywords)) {
            $queryBuilder->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('title', 'like', "%{$word}%")
                      ->orWhere('skills', 'like', "%{$word}%")
                      ->orWhere('location', 'like', "%{$word}%")
                      ->orWhere('job_type', 'like', "%{$word}%")
                      ->orWhere('description', 'like', "%{$word}%");
                }
            });
        }

        $results = $queryBuilder->latest('id')->take(4)->get();

        // If no keyword match or no keywords, fallback to latest active jobs if query asks about jobs or careers
        if ($results->isEmpty() && (preg_match('/job|open|hiring|work|career|position|vacancy|role/i', $query))) {
            $results = JobPosting::query()
                ->where('status', 'active')
                ->with(['user.companyProfile'])
                ->latest('id')
                ->take(3)
                ->get();
        }

        return $results;
    }

    /**
     * Format a job model for JSON output.
     */
    protected function formatJobPayload(JobPosting $job): array
    {
        $companyName = $job->user?->companyProfile?->company_name ?: ($job->user?->name ?: 'Hiring Company');
        return [
            'id' => $job->id,
            'title' => $job->title,
            'company' => $companyName,
            'location' => $job->location ?: 'Remote / Hybrid',
            'job_type' => $job->job_type ?: 'Full-time',
            'salary_range' => $job->salary_range ?: 'Competitive',
            'skills' => $job->skills ?: '',
            'url' => route('jobs.public.show', $job->id),
            'has_whatsapp' => !empty($job->whatsapp_number),
        ];
    }

    /**
     * Construct rich grounding system prompt.
     */
    protected function buildSystemPrompt(?User $user, Collection $matchedJobs): string
    {
        $prompt = "You are the intelligent, helpful, and friendly AI Assistant for 'AI Looping', a next-generation AI recruitment and candidate discovery platform.\n\n";

        $prompt .= "=== WEBSITE CAPABILITIES & STRUCTURE ===\n";
        $prompt .= "- General & Public:\n";
        $prompt .= "  * Browse Verified Scouted Jobs: [Explore Jobs](/jobs)\n";
        $prompt .= "  * Candidate Direct WhatsApp Inquiries: Candidates can send direct WhatsApp messages to hiring managers on active job postings.\n";
        $prompt .= "  * Contact & Support: [Contact Page](/contact) for help or inquiries.\n";
        $prompt .= "  * Auth: [Login](/login) | [Register](/register)\n";
        $prompt .= "- For Candidates:\n";
        $prompt .= "  * Candidate Dashboard: [Dashboard](/dashboard)\n";
        $prompt .= "  * AI Resume Tailoring: [Tailor Resume](/tailor-resume) to customize CV for specific job descriptions.\n";
        $prompt .= "  * AI Interview Preparation: [Interview Prep](/interview-prep) with generated mock questions and feedback.\n";
        $prompt .= "  * Job Campaigns: [Campaigns](/campaigns) for automated multi-channel job outreach.\n";
        $prompt .= "- For Companies & Recruiters:\n";
        $prompt .= "  * Company Dashboard: [Company Dashboard](/company/dashboard)\n";
        $prompt .= "  * 1-Click Multi-Board Job Posting: [Post a Job](/company/jobs/create) to distribute across LinkedIn, Indeed, and Naukri.\n";
        $prompt .= "  * Job Postings Management: [Company Jobs](/company/jobs)\n";
        $prompt .= "  * Centralized Candidate Inquiries Hub: [Candidate Inquiries](/company/inquiries) (review comments, reply via Email or WhatsApp, pin high-priority questions).\n";
        $prompt .= "  * AI Headhunter Agent: Scouts top candidate matches and generates personalized outreach.\n";
        $prompt .= "  * Platform Integrations: [Integrations](/company/integrations) (LinkedIn OAuth, Indeed ATS XML Feed, Naukri).\n\n";

        $prompt .= "=== CURRENT USER CONTEXT ===\n";
        if (!$user) {
            $prompt .= "User Status: Unauthenticated Guest / Visitor.\n";
            $prompt .= "Guidance: Be warm, invite them to browse verified jobs at `/jobs`, or sign up at `/register` to unlock AI resume tailoring, automated campaigns, or 1-click recruiter job posting.\n";
        } elseif ($user->isCompany()) {
            $companyName = $user->companyProfile?->company_name ?: $user->name;
            $postedJobsCount = $user->jobPostings()->count();
            $prompt .= "User Status: Logged in Company / Recruiter ({$companyName}).\n";
            $prompt .= "Active Jobs Posted: {$postedJobsCount}.\n";
            $prompt .= "Guidance: Assist them with talent scouting, 1-click posting to LinkedIn/Indeed, managing candidate inquiries via WhatsApp, and shortlisting applicants.\n";
        } else {
            $prompt .= "User Status: Logged in Candidate ({$user->name}, {$user->email}).\n";
            $prompt .= "Guidance: Assist them in finding matching job roles, tailoring their CV at `/tailor-resume`, preparing for interviews at `/interview-prep`, or reaching out to recruiters on WhatsApp.\n";
        }
        $prompt .= "\n";

        if ($matchedJobs->isNotEmpty()) {
            $prompt .= "=== CURRENT MATCHING JOBS ON THE PLATFORM (REAL DATA) ===\n";
            foreach ($matchedJobs as $job) {
                $comp = $job->user?->companyProfile?->company_name ?: ($job->user?->name ?: 'Hiring Team');
                $prompt .= "- **{$job->title}** at {$comp} | Location: {$job->location} | Type: {$job->job_type} | Salary: {$job->salary_range} | Skills: {$job->skills} | Link: [View Job](/jobs/{$job->id})\n";
            }
            $prompt .= "If relevant to the user query, present these exact jobs and highlight their link `[View Job](/jobs/{id})`!\n\n";
        }

        $prompt .= "=== RESPONSE GUIDELINES ===\n";
        $prompt .= "1. Maintain a professional, encouraging, and concise tone.\n";
        $prompt .= "2. Structure responses cleanly using bullet points, short paragraphs, and bold text for readability.\n";
        $prompt .= "3. Include Markdown links (e.g. `[Explore Jobs](/jobs)`, `[Tailor Resume](/tailor-resume)`, `[Company Inquiries](/company/inquiries)`) so users can click directly.\n";
        $prompt .= "4. BRANDING RULE: Strictly refer to messaging as 'WhatsApp'. NEVER mention 'Twilio' or internal infrastructure.\n";
        $prompt .= "5. If asked something completely unrelated to jobs, career, hiring, or the platform, answer briefly and politely steer back to career/recruitment assistance.\n";

        return $prompt;
    }

    /**
     * Friendly fallback when Groq API is unavailable.
     */
    protected function getFallbackReply(string $query, Collection $matchedJobs): string
    {
        $reply = "I'm here to help! While my AI reasoning engine is momentarily refreshing, here are quick shortcuts for you:\n\n";

        if ($matchedJobs->isNotEmpty()) {
            $reply .= "### Current Open Roles:\n";
            foreach ($matchedJobs as $job) {
                $comp = $job->user?->companyProfile?->company_name ?: 'Hiring Team';
                $reply .= "• **[{$job->title}](/jobs/{$job->id})** at {$comp} ({$job->location})\n";
            }
            $reply .= "\nCheck out all active openings on our **[Jobs Board](/jobs)**.\n\n";
        } else {
            $reply .= "• **[Browse Scouted Jobs](/jobs)** — Explore verified openings with direct WhatsApp contact\n";
            $reply .= "• **[AI Resume Tailoring](/tailor-resume)** — Optimize your CV for modern ATS filters\n";
            $reply .= "• **[1-Click Job Posting](/company/jobs/create)** — Distribute jobs across LinkedIn, Indeed, and Naukri\n";
            $reply .= "• **[Contact Support](/contact)** — Get in touch with our team\n\n";
        }

        $reply .= "Feel free to ask another question or explore the links above!";
        return $reply;
    }

    /**
     * Get initial greeting and starter suggestion chips for the client.
     */
    public function getInitialContext(?User $user): array
    {
        if (!$user) {
            return [
                'role' => 'guest',
                'user_name' => 'Guest',
                'welcome_message' => "Hello! 👋 Welcome to **AI Looping**. I'm your AI Assistant. I can help you find verified job openings, explain our AI scouting features, or guide you around the platform. What can I do for you today?",
                'suggestions' => [
                    '🔍 Explore active jobs',
                    '⚡ How does AI Looping work?',
                    '💼 How do I post a job?',
                    '📱 How does WhatsApp contact work?',
                ],
            ];
        }

        if ($user->isCompany()) {
            $companyName = $user->companyProfile?->company_name ?: $user->name;
            return [
                'role' => 'company',
                'user_name' => $companyName,
                'welcome_message' => "Welcome, **{$companyName}**! 💼 I'm your dedicated Recruitment Assistant. I can help you scout top candidate matches, post jobs across LinkedIn & Indeed, or manage candidate inquiries.",
                'suggestions' => [
                    '🚀 Post a new job',
                    '🎯 Scout top candidates',
                    '💬 Manage candidate inquiries',
                    '📊 Check integrations status',
                ],
            ];
        }

        return [
            'role' => 'candidate',
            'user_name' => $user->name,
            'welcome_message' => "Hi **{$user->name}**! 🚀 I'm your Career & Job Search Assistant. Looking for new verified openings, need resume tailoring, or preparing for an upcoming interview?",
            'suggestions' => [
                '🔍 Find developer jobs',
                '📄 Tailor my resume',
                '🎯 Mock interview questions',
                '💬 How to contact recruiters directly',
            ],
        ];
    }
}
