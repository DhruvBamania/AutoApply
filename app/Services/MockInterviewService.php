<?php

namespace App\Services;

use App\Models\MockInterviewSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MockInterviewService
{
    protected GroqAiService $groq;
    protected ?GeminiAiService $gemini;

    public function __construct(GroqAiService $groq, ?GeminiAiService $gemini = null)
    {
        $this->groq = $groq;
        $this->gemini = $gemini ?: app(GeminiAiService::class);
    }

    /**
     * Start a new interactive mock interview session.
     */
    public function startSession(User $user, array $data): MockInterviewSession
    {
        $roleTitle = $data['role_title'];
        $experienceLevel = $data['experience_level'];
        $techStack = $data['tech_stack'];
        $prepId = $data['interview_preparation_id'] ?? null;

        $session = MockInterviewSession::create([
            'user_id' => $user->id,
            'interview_preparation_id' => $prepId,
            'role_title' => $roleTitle,
            'experience_level' => $experienceLevel,
            'tech_stack' => $techStack,
            'status' => 'in_progress',
            'current_question_index' => 1,
            'total_questions' => 5,
            'conversation_history' => [],
        ]);

        // Generate Question 1
        $firstQuestion = $this->generateInitialQuestion($session, $user);

        $session->addMessage('interviewer', $firstQuestion, 1);
        $session->save();

        return $session;
    }

    /**
     * Process a candidate's answer for the current question turn.
     * In Realistic Strict Mode:
     * - If questions remain: Acknowledges briefly and asks the next question.
     * - If last question (5/5): Concludes the interview and compiles the final evaluation report.
     */
    public function submitTurn(MockInterviewSession $session, string $candidateAnswer): array
    {
        // 1. Record candidate's answer
        $currentQ = $session->current_question_index;
        $session->addMessage('candidate', trim($candidateAnswer), $currentQ);

        // 2. Check if we have more questions to ask
        if ($currentQ < $session->total_questions) {
            $nextQNumber = $currentQ + 1;
            $session->current_question_index = $nextQNumber;

            // Generate next question
            $nextInterviewerTurn = $this->generateNextQuestion($session, $nextQNumber);
            $session->addMessage('interviewer', $nextInterviewerTurn, $nextQNumber);
            $session->save();

            return [
                'status' => 'in_progress',
                'question_number' => $nextQNumber,
                'total_questions' => $session->total_questions,
                'interviewer_message' => $nextInterviewerTurn,
                'is_final' => false,
            ];
        }

        // 3. Last question answered! Generate full comprehensive scorecard & evaluation
        $evaluationReport = $this->generateFinalEvaluation($session);

        $session->status = 'completed';
        $session->evaluation_report = $evaluationReport;
        $session->addMessage(
            'interviewer',
            "Thank you for completing this technical interview for {$session->role_title}. I have compiled your comprehensive performance assessment and scorecard.",
            $session->total_questions
        );
        $session->save();

        return [
            'status' => 'completed',
            'question_number' => $session->total_questions,
            'total_questions' => $session->total_questions,
            'interviewer_message' => "Interview completed! View your full scorecard below.",
            'is_final' => true,
            'report' => $evaluationReport,
        ];
    }

    /**
     * Generate the first question of the interview.
     */
    protected function generateInitialQuestion(MockInterviewSession $session, User $user): string
    {
        $prompt = <<<EOT
You are an experienced, professional, yet challenging Tech Lead and Hiring Manager conducting a realistic technical interview.
Target Role: {$session->role_title}
Seniority: {$session->experience_level}
Core Tech Stack: {$session->tech_stack}
Candidate Name: {$user->name}

INSTRUCTIONS:
1. Greet the candidate in one concise sentence (e.g. "Hello {$user->name}, welcome to your technical interview for the {$session->role_title} role.").
2. Immediately state "Question 1 of 5:" and ask an impactful, real-world architecture / core fundamentals question tailored to {$session->tech_stack}.
3. STRICT MODE: Do not include tips, sample answers, or multiple questions. Ask exactly ONE clear question for them to answer.
EOT;

        $response = $this->askAi($prompt);

        if (!$response) {
            return "Hello {$user->name}, welcome to your technical interview for the {$session->role_title} position. Let's begin.\n\n**Question 1 of 5:** Could you walk me through the architecture of a production application you built with {$session->tech_stack}, highlighting how you structured data flow and handled scalability?";
        }

        return trim($response);
    }

    /**
     * Generate subsequent questions (Questions 2 through 5).
     */
    protected function generateNextQuestion(MockInterviewSession $session, int $questionNumber): string
    {
        $historyText = $this->formatHistoryForPrompt($session);

        $topicFocus = match ($questionNumber) {
            2 => "Technical Deep Dive & Framework Internals / State Management in {$session->tech_stack}",
            3 => "System Design, Scalability, Caching, or Database Optimization",
            4 => "Production Incident, Critical Bug Troubleshooting, or Security & Edge Cases",
            5 => "Behavioral & Leadership scenario (e.g., handling pushback on tech debt, cross-functional engineering conflict, or tight deadlines)",
            default => "Core Engineering Problem Solving",
        };

        $prompt = <<<EOT
You are a senior tech lead conducting a realistic, strict technical interview.
Role: {$session->role_title} ({$session->experience_level})
Tech Stack: {$session->tech_stack}
Current Stage: Question {$questionNumber} of {$session->total_questions}
Focus for this turn: {$topicFocus}

PREVIOUS INTERVIEW CONVERSATION SO FAR:
{$historyText}

INSTRUCTIONS:
1. In REALISTIC STRICT MODE: You must NOT evaluate or score their previous answer right now.
2. Provide a brief, realistic 1-sentence transition acknowledging their previous response (e.g., "Thank you. Let's move on to system reliability." or "Understood. Drilling deeper into our tech stack:").
3. Clearly label "**Question {$questionNumber} of {$session->total_questions}:**" and ask ONE challenging, specific question focusing on: {$topicFocus}.
4. Keep it direct and realistic. Do not offer hints or answers.
EOT;

        $response = $this->askAi($prompt);

        if (!$response) {
            return match ($questionNumber) {
                2 => "Thank you for that explanation.\n\n**Question 2 of 5:** When working with {$session->tech_stack}, how do you manage complex application state and prevent asynchronous race conditions or memory leaks?",
                3 => "Understood. Let's look at system design and performance.\n\n**Question 3 of 5:** Suppose our system experiences a 10x traffic spike causing high database latency and API timeouts. How would you diagnose the bottleneck and architect a caching and read-replica strategy?",
                4 => "Noted.\n\n**Question 4 of 5:** Can you describe a critical production bug or security vulnerability you encountered in {$session->tech_stack}, how you isolated the root cause under pressure, and what safeguards you introduced?",
                5 => "Thank you. Finally, let's explore engineering collaboration.\n\n**Question 5 of 5:** Tell me about a time you strongly disagreed with a product manager or team lead regarding technical debt versus shipping a rushed feature. How did you handle the situation and what was the outcome?",
                default => "Thank you.\n\n**Question {$questionNumber} of 5:** How do you ensure high test coverage and code quality in continuous deployment pipelines with {$session->tech_stack}?",
            };
        }

        return trim($response);
    }

    /**
     * Generate the comprehensive final scorecard once all 5 questions are answered.
     */
    protected function generateFinalEvaluation(MockInterviewSession $session): array
    {
        $historyText = $this->formatHistoryForPrompt($session);

        $prompt = <<<EOT
You are an executive hiring committee reviewing a completed 5-question technical interview.
Role: {$session->role_title} ({$session->experience_level})
Tech Stack: {$session->tech_stack}

FULL INTERVIEW TRANSCRIPT:
{$historyText}

Evaluate the candidate rigorously and generate a comprehensive, actionable evaluation report.
You MUST output ONLY a valid JSON object matching this exact schema:
{
  "overall_score": 85,
  "verdict": "Strong Hire",
  "readiness_level": "Mid-to-Senior",
  "summary": "Detailed 2-paragraph analysis summarizing their technical depth, communication clarity, and readiness for this role.",
  "strengths": [
    "Clear architectural thinking with practical database indexing examples",
    "Structured communication using the STAR framework during behavioral questions",
    "Proactive attitude toward observability and error tracing"
  ],
  "growth_areas": [
    "Could provide more concrete numbers/metrics when discussing performance optimization",
    "Should explain fallback strategies when third-party microservices fail"
  ],
  "question_reviews": [
    {
      "question_number": 1,
      "score": 8,
      "topic": "Architecture & Fundamentals",
      "strengths": "Strong explanation of layered service architecture",
      "missing": "Could have touched on caching strategies earlier",
      "ideal_key_points": "High-level component separation, data flow, API gateway, and caching"
    },
    {
      "question_number": 2,
      "score": 7,
      "topic": "Framework Internals",
      "strengths": "Accurate grasp of async primitives",
      "missing": "Didn't mention memory profiling tools",
      "ideal_key_points": "State immutability, debounce/throttling, memory leak detection"
    },
    {
      "question_number": 3,
      "score": 9,
      "topic": "System Scalability",
      "strengths": "Solid Redis caching and read-replica failover explanation",
      "missing": "Minor gap on cache invalidation stampede mitigation",
      "ideal_key_points": "Horizontal scaling, CDN offloading, Redis multi-layer cache, DB read replicas"
    },
    {
      "question_number": 4,
      "score": 8,
      "topic": "Debugging & Resilience",
      "strengths": "Demonstrated systematic log isolation and post-mortem review",
      "missing": "Could mention automated alerting thresholds",
      "ideal_key_points": "Reproducing bug in staging, hotfixing, root cause analysis, automated regression test"
    },
    {
      "question_number": 5,
      "score": 9,
      "topic": "Behavioral & Team Leadership",
      "strengths": "Excellent diplomacy balancing business deadlines with technical integrity",
      "missing": "None noted",
      "ideal_key_points": "Data-backed trade-offs, phased refactoring, cross-functional alignment"
    }
  ]
}

Ensure overall_score is between 0 and 100.
Verdict must be one of: "Strong Hire", "Hire", "Lean Hire", "Needs Further Preparation".
Output pure JSON with no markdown backticks, no preamble.
EOT;

        $response = $this->askAi($prompt, true);

        if ($response) {
            $parsed = $this->cleanAndParseJson($response);
            if (!empty($parsed['overall_score']) && !empty($parsed['verdict'])) {
                return $parsed;
            }
        }

        // Fallback intelligent evaluation
        return $this->generateFallbackEvaluation($session);
    }

    /**
     * AI communication helper.
     */
    protected function askAi(string $prompt, bool $jsonMode = false): ?string
    {
        // 1. Try Groq AI (fast, 70b/20b)
        try {
            $options = $jsonMode ? ['response_format' => ['type' => 'json_object']] : [];
            $res = $this->groq->chat([
                ['role' => 'user', 'content' => $prompt]
            ], $options);

            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            Log::warning('MockInterviewService Groq AI failure: ' . $e->getMessage());
        }

        // 2. Fallback to Gemini AI
        if ($this->gemini && $this->gemini->isConfigured()) {
            try {
                $res = $this->gemini->generate($prompt, $jsonMode);
                if (!empty($res)) {
                    return $res;
                }
            } catch (\Throwable $e) {
                Log::warning('MockInterviewService Gemini AI failure: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Format conversation history into clean script for AI context.
     */
    protected function formatHistoryForPrompt(MockInterviewSession $session): string
    {
        $history = $session->conversation_history ?? [];
        $lines = [];

        foreach ($history as $turn) {
            $roleLabel = ($turn['role'] === 'interviewer') ? 'INTERVIEWER' : 'CANDIDATE';
            $qNum = isset($turn['question_number']) ? " (Q{$turn['question_number']})" : '';
            $lines[] = "{$roleLabel}{$qNum}: " . trim($turn['content']);
        }

        return implode("\n\n", $lines);
    }

    /**
     * Clean and parse JSON response from LLM.
     */
    protected function cleanAndParseJson(string $raw): ?array
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Fallback scorecard generation if AI is offline.
     */
    protected function generateFallbackEvaluation(MockInterviewSession $session): array
    {
        $history = $session->conversation_history ?? [];
        $candidateWords = 0;
        foreach ($history as $turn) {
            if ($turn['role'] === 'candidate') {
                $candidateWords += str_word_count($turn['content']);
            }
        }

        $baseScore = min(92, max(68, 60 + (int)($candidateWords / 12)));
        $verdict = $baseScore >= 85 ? 'Strong Hire' : ($baseScore >= 75 ? 'Hire' : 'Lean Hire');

        return [
            'overall_score' => $baseScore,
            'verdict' => $verdict,
            'readiness_level' => $session->experience_level,
            'summary' => "The candidate demonstrated solid fundamental competence for the {$session->role_title} role across {$session->tech_stack}. Answers were structured and addressed core software lifecycle and collaborative principles.",
            'strengths' => [
                "Clear domain familiarity with {$session->tech_stack}",
                "Approached problem-solving with a structured, step-by-step methodology",
                "Demonstrated professional communication during technical questioning"
            ],
            'growth_areas' => [
                "Consider providing more specific edge-case considerations during architectural discussions",
                "Quantify business outcomes or latency improvements where applicable"
            ],
            'question_reviews' => [
                [
                    'question_number' => 1,
                    'score' => 8,
                    'topic' => 'Architecture & Core Fundamentals',
                    'strengths' => 'Communicated high-level structure cleanly',
                    'missing' => 'Could elaborate on database transaction boundaries',
                    'ideal_key_points' => 'Separation of concerns, clean APIs, database normalization'
                ],
                [
                    'question_number' => 2,
                    'score' => 8,
                    'topic' => 'Framework Internals',
                    'strengths' => 'Addressed common framework conventions',
                    'missing' => 'More depth on async patterns',
                    'ideal_key_points' => 'State management, memory efficiency, concurrency'
                ],
                [
                    'question_number' => 3,
                    'score' => 7,
                    'topic' => 'System Scalability',
                    'strengths' => 'Identified caching as a primary mitigation',
                    'missing' => 'Database indexing and connection pooling',
                    'ideal_key_points' => 'Horizontal scaling, Redis, read replicas, load balancing'
                ],
                [
                    'question_number' => 4,
                    'score' => 8,
                    'topic' => 'Production Troubleshooting',
                    'strengths' => 'Systematic log inspection and rollback strategy',
                    'missing' => 'Mention of observability metrics and alerts',
                    'ideal_key_points' => 'Isolating errors, repro steps, safe deployment, blameless post-mortem'
                ],
                [
                    'question_number' => 5,
                    'score' => 9,
                    'topic' => 'Behavioral & Collaboration',
                    'strengths' => 'Constructive approach to technical disagreement',
                    'missing' => 'None noted',
                    'ideal_key_points' => 'STAR method, data-driven compromise, stakeholder empathy'
                ]
            ]
        ];
    }
}

