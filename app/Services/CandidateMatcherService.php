<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class CandidateMatcherService
{
    protected GeminiAiService $gemini;
    protected ?GroqAiService $groq;

    public function __construct(GeminiAiService $gemini, ?GroqAiService $groq = null)
    {
        $this->gemini = $gemini;
        $this->groq = $groq ?: app(GroqAiService::class);
    }

    /**
     * Analyze and rank all candidates for a specific job posting.
     *
     * @param JobPosting $job
     * @return Collection
     */
    public function matchAndRankCandidates(JobPosting $job): Collection
    {
        // Get all inquiries (applications, queries, comments) for this job
        $inquiries = $job->inquiries()->get();

        if ($inquiries->isEmpty()) {
            return collect();
        }

        // Prepare candidate payloads with extracted resume/profile text
        $candidatesData = [];
        foreach ($inquiries as $inquiry) {
            $extractedText = $this->extractCandidateResumeText($inquiry);
            $candidatesData[] = [
                'id' => $inquiry->id,
                'name' => $inquiry->candidate_name,
                'headline' => $inquiry->candidate_headline,
                'email' => $inquiry->candidate_email,
                'platform' => $inquiry->platform,
                'message' => $inquiry->message,
                'has_resume' => !empty($inquiry->resume_path),
                'resume_text' => $extractedText,
            ];
        }

        // Attempt Gemini AI matching
        $matchResults = $this->evaluateWithGemini($job, $candidatesData);

        // Fallback to Groq or heuristic matcher if Gemini fails or is unconfigured
        if (empty($matchResults)) {
            $matchResults = $this->evaluateWithGroqFallback($job, $candidatesData);
        }

        if (empty($matchResults)) {
            $matchResults = $this->evaluateWithAlgorithmicMatcher($job, $candidatesData);
        }

        // Sort results by match_score DESC, skills_score DESC, experience_score DESC
        usort($matchResults, function ($a, $b) {
            if (($b['match_score'] ?? 0) !== ($a['match_score'] ?? 0)) {
                return ($b['match_score'] ?? 0) <=> ($a['match_score'] ?? 0);
            }
            if (($b['skills_score'] ?? 0) !== ($a['skills_score'] ?? 0)) {
                return ($b['skills_score'] ?? 0) <=> ($a['skills_score'] ?? 0);
            }
            return ($b['experience_score'] ?? 0) <=> ($a['experience_score'] ?? 0);
        });

        // Assign ranks (1..N) and persist to database
        $rankedCollection = collect();
        $rank = 1;

        foreach ($matchResults as $result) {
            $inquiry = $inquiries->firstWhere('id', $result['candidate_id']);
            if ($inquiry) {
                $inquiry->update([
                    'ai_match_score' => $result['match_score'],
                    'ai_rank' => $rank,
                    'ai_experience_score' => $result['experience_score'],
                    'ai_skills_score' => $result['skills_score'],
                    'ai_projects_score' => $result['projects_score'],
                    'ai_recommendation' => $result['recommendation'],
                    'ai_summary' => $result['summary'],
                    'ai_matched_skills' => $result['matched_skills'],
                    'ai_missing_skills' => $result['missing_skills'],
                    'ai_analyzed_at' => now(),
                ]);

                $rankedCollection->push($inquiry);
                $rank++;
            }
        }

        $job->update(['last_ai_match_at' => now()]);

        return $rankedCollection;
    }

    /**
     * Batch shortlist top matching candidates (score >= threshold).
     *
     * @param JobPosting $job
     * @param int $threshold
     * @return int Count of newly shortlisted candidates
     */
    public function shortlistTopMatches(JobPosting $job, int $threshold = 75): int
    {
        $eligible = $job->inquiries()
            ->whereNotNull('ai_match_score')
            ->where('ai_match_score', '>=', $threshold)
            ->where('status', '!=', 'shortlisted')
            ->get();

        foreach ($eligible as $inquiry) {
            $inquiry->update(['status' => 'shortlisted']);
        }

        return $eligible->count();
    }

    /**
     * Extract resume text from uploaded PDF / DOCX file or fallback to candidate profile/message.
     */
    public function extractCandidateResumeText(JobInquiry $inquiry): string
    {
        $text = '';

        if ($inquiry->resume_path) {
            $disk = Storage::disk('public');
            if ($disk->exists($inquiry->resume_path)) {
                $fullPath = $disk->path($inquiry->resume_path);
                $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                try {
                    if ($extension === 'pdf') {
                        $parser = new PdfParser();
                        $pdf = $parser->parseFile($fullPath);
                        $text = $pdf->getText();
                    } elseif ($extension === 'docx') {
                        $zip = new \ZipArchive();
                        if ($zip->open($fullPath) === true) {
                            $xml = $zip->getFromName('word/document.xml');
                            $zip->close();
                            if ($xml) {
                                $text = strip_tags(str_replace(['<w:p', '</w:p>'], ["\n<w:p", "\n"], $xml));
                            }
                        }
                    } elseif (in_array($extension, ['txt', 'rtf'])) {
                        $text = file_get_contents($fullPath);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Failed extracting text from resume {$inquiry->resume_path}: " . $e->getMessage());
                }
            }
        }

        // Clean extracted text or fallback to message + headline
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (empty($text) || strlen($text) < 30) {
            $headline = $inquiry->candidate_headline ? "Headline: {$inquiry->candidate_headline}. " : "";
            $text = "Candidate Name: {$inquiry->candidate_name}. {$headline}Message/Application: {$inquiry->message}";
        }

        // Truncate to reasonable length for LLM context efficiency (up to 4000 characters per resume)
        return mb_substr($text, 0, 4000);
    }

    /**
     * Evaluate candidates using Gemini AI API.
     */
    protected function evaluateWithGemini(JobPosting $job, array $candidatesData): ?array
    {
        if (!$this->gemini->isConfigured()) {
            return null;
        }

        $prompt = $this->buildEvaluationPrompt($job, $candidatesData);
        $response = $this->gemini->generate($prompt, true);

        if (!$response) {
            return null;
        }

        return $this->parseJsonResponse($response, $candidatesData);
    }

    /**
     * Evaluate candidates using Groq AI fallback.
     */
    protected function evaluateWithGroqFallback(JobPosting $job, array $candidatesData): ?array
    {
        if (!$this->groq || !config('services.groq.key')) {
            return null;
        }

        try {
            $prompt = $this->buildEvaluationPrompt($job, $candidatesData);
            $response = $this->groq->generate($prompt);

            if (!$response) {
                return null;
            }

            return $this->parseJsonResponse($response, $candidatesData);
        } catch (\Throwable $e) {
            Log::warning("Groq AI fallback matching failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Build the structured prompt for candidate evaluation and ranking.
     */
    protected function buildEvaluationPrompt(JobPosting $job, array $candidatesData): string
    {
        $skillsList = $job->skills ?: 'Not specifically listed';
        $expRequired = $job->experience_required ?: 'Not specified';

        $prompt = "You are an elite AI Technical Recruiter and Talent Matcher.\n"
            . "Analyze and evaluate each candidate's resume/application against the specified Job Role requirements.\n\n"
            . "=== TARGET JOB ROLE ===\n"
            . "Job Title: {$job->title}\n"
            . "Job Type: {$job->job_type}\n"
            . "Location: {$job->location}\n"
            . "Required Experience: {$expRequired}\n"
            . "Required Key Skills: {$skillsList}\n"
            . "Job Description:\n{$job->description}\n\n"
            . "=== CANDIDATES TO EVALUATE ===\n";

        foreach ($candidatesData as $c) {
            $prompt .= "\n--- Candidate ID: {$c['id']} ---\n"
                . "Name: {$c['name']}\n"
                . "Headline: {$c['headline']}\n"
                . "Has Uploaded Resume: " . ($c['has_resume'] ? 'Yes' : 'No') . "\n"
                . "Resume / Experience Profile:\n{$c['resume_text']}\n";
        }

        $prompt .= "\n=== EVALUATION CRITERIA ===\n"
            . "For each candidate, evaluate:\n"
            . "1. experience_score (0-100): Alignment with years of experience, role seniority, and domain.\n"
            . "2. skills_score (0-100): Overlap with the job's required skills and tech stack.\n"
            . "3. projects_score (0-100): Strength of demonstrated projects, deliverables, and practical output.\n"
            . "4. match_score (0-100): Overall composite fit (e.g. 40% skills, 35% experience, 25% projects).\n"
            . "5. recommendation: One of 'Strong Match', 'Good Match', 'Moderate Match', 'Low Match'.\n"
            . "6. summary: 2-3 concise sentences highlighting why this candidate matches or where gaps exist.\n"
            . "7. matched_skills: Array of skills from the job description that the candidate demonstrably has.\n"
            . "8. missing_skills: Array of required skills that are missing or lacking in the candidate profile.\n\n"
            . "Respond ONLY with a valid JSON array of objects with keys:\n"
            . "candidate_id (integer), match_score (integer), experience_score (integer), skills_score (integer), "
            . "projects_score (integer), recommendation (string), summary (string), matched_skills (array of strings), "
            . "missing_skills (array of strings).\n"
            . "Do not include markdown codeblocks or explanation text.";

        return $prompt;
    }

    /**
     * Parse and sanitize JSON response from LLM.
     */
    protected function parseJsonResponse(string $rawText, array $candidatesData): ?array
    {
        $rawText = trim($rawText);

        // Strip markdown fences if present
        if (str_starts_with($rawText, '```json')) {
            $rawText = substr($rawText, 7);
        }
        if (str_starts_with($rawText, '```')) {
            $rawText = substr($rawText, 3);
        }
        if (str_ends_with($rawText, '```')) {
            $rawText = substr($rawText, 0, -3);
        }
        $rawText = trim($rawText);

        // Regex match JSON array if surrounded by other text
        if (preg_match('/\[[\s\S]*\]/', $rawText, $matches)) {
            $rawText = $matches[0];
        }

        $decoded = json_decode($rawText, true);

        if (!is_array($decoded) || empty($decoded)) {
            return null;
        }

        $results = [];
        $validIds = array_column($candidatesData, 'id');

        foreach ($decoded as $item) {
            if (!isset($item['candidate_id'])) {
                continue;
            }

            $cid = (int)$item['candidate_id'];
            if (!in_array($cid, $validIds)) {
                continue;
            }

            $matchScore = max(0, min(100, (int)($item['match_score'] ?? 50)));
            $expScore = max(0, min(100, (int)($item['experience_score'] ?? $matchScore)));
            $skillsScore = max(0, min(100, (int)($item['skills_score'] ?? $matchScore)));
            $projScore = max(0, min(100, (int)($item['projects_score'] ?? $matchScore)));

            $rec = $item['recommendation'] ?? ($matchScore >= 80 ? 'Strong Match' : ($matchScore >= 65 ? 'Good Match' : ($matchScore >= 50 ? 'Moderate Match' : 'Low Match')));

            $results[] = [
                'candidate_id' => $cid,
                'match_score' => $matchScore,
                'experience_score' => $expScore,
                'skills_score' => $skillsScore,
                'projects_score' => $projScore,
                'recommendation' => $rec,
                'summary' => $item['summary'] ?? 'AI analyzed candidate qualifications and experience against job specifications.',
                'matched_skills' => is_array($item['matched_skills'] ?? null) ? $item['matched_skills'] : [],
                'missing_skills' => is_array($item['missing_skills'] ?? null) ? $item['missing_skills'] : [],
            ];
        }

        return !empty($results) ? $results : null;
    }

    /**
     * Intelligent algorithmic evaluation fallback (used if API key is not present or offline).
     */
    protected function evaluateWithAlgorithmicMatcher(JobPosting $job, array $candidatesData): array
    {
        $requiredSkills = [];
        if ($job->skills) {
            $requiredSkills = array_filter(array_map('trim', explode(',', strtolower($job->skills))));
        }

        // Also extract common keywords from title and description
        $roleTitleWords = array_filter(explode(' ', strtolower($job->title)), fn($w) => strlen($w) > 3);
        $allTargetKeywords = array_unique(array_merge($requiredSkills, $roleTitleWords));

        $results = [];

        foreach ($candidatesData as $candidate) {
            $fullText = strtolower($candidate['headline'] . ' ' . $candidate['message'] . ' ' . $candidate['resume_text']);

            $matchedSkills = [];
            $missingSkills = [];

            foreach ($allTargetKeywords as $skill) {
                if (str_contains($fullText, $skill)) {
                    $matchedSkills[] = ucfirst($skill);
                } else {
                    $missingSkills[] = ucfirst($skill);
                }
            }

            $totalKeywords = max(1, count($allTargetKeywords));
            $matchRatio = count($matchedSkills) / $totalKeywords;

            // Base skills score
            $skillsScore = (int)round($matchRatio * 70 + 25);
            $skillsScore = max(35, min(98, $skillsScore));

            // Experience score based on indicators like "senior", "lead", years of exp
            $expScore = 60;
            if (preg_match('/(\d+)\+?\s*(?:years?|yrs?)/i', $fullText, $matches)) {
                $years = (int)$matches[1];
                $expScore = min(96, max(40, 50 + ($years * 7)));
            } elseif (str_contains($fullText, 'senior') || str_contains($fullText, 'lead') || str_contains($fullText, 'architect')) {
                $expScore = 88;
            } elseif (str_contains($fullText, 'intern') || str_contains($fullText, 'fresher') || str_contains($fullText, 'junior')) {
                $expScore = 55;
            }

            // Projects score
            $projScore = $candidate['has_resume'] ? min(95, (int)round(($skillsScore + $expScore) / 2 + 5)) : 50;

            // Overall score
            $matchScore = (int)round(($skillsScore * 0.45) + ($expScore * 0.35) + ($projScore * 0.20));
            $matchScore = max(30, min(98, $matchScore));

            if ($matchScore >= 80) {
                $rec = 'Strong Match';
                $summary = "Candidate demonstrates strong overlap with required core skills (" . implode(', ', array_slice($matchedSkills, 0, 3)) . ") and solid relevant experience.";
            } elseif ($matchScore >= 65) {
                $rec = 'Good Match';
                $summary = "Candidate demonstrates relevant background with key capabilities (" . implode(', ', array_slice($matchedSkills, 0, 2)) . "), with potential areas for growth in " . (count($missingSkills) ? $missingSkills[0] : 'advanced specialization') . ".";
            } elseif ($matchScore >= 50) {
                $rec = 'Moderate Match';
                $summary = "Candidate fulfills foundational qualifications but shows gaps in required specialized skills.";
            } else {
                $rec = 'Low Match';
                $summary = "Candidate profile shows limited alignment with key technical proficiencies and role seniority.";
            }

            $results[] = [
                'candidate_id' => $candidate['id'],
                'match_score' => $matchScore,
                'experience_score' => $expScore,
                'skills_score' => $skillsScore,
                'projects_score' => $projScore,
                'recommendation' => $rec,
                'summary' => $summary,
                'matched_skills' => array_values(array_slice($matchedSkills, 0, 6)),
                'missing_skills' => array_values(array_slice($missingSkills, 0, 4)),
            ];
        }

        return $results;
    }
}
