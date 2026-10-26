<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class InterviewPrepService
{
    protected GeminiAiService $gemini;
    protected ?GroqAiService $groq;

    public function __construct(GeminiAiService $gemini, ?GroqAiService $groq = null)
    {
        $this->gemini = $gemini;
        $this->groq = $groq ?: app(GroqAiService::class);
    }

    /**
     * Generate dynamic, topic-wise interview preparation questions tailored to the role,
     * tech stack, and experience level.
     *
     * @param string $roleTitle
     * @param string $experienceLevel
     * @param string $techStack
     * @param string|null $resumeContext
     * @return array
     */
    public function generatePrep(
        string $roleTitle,
        string $experienceLevel,
        string $techStack,
        ?string $resumeContext = null
    ): array {
        $prompt = $this->buildPrompt($roleTitle, $experienceLevel, $techStack, $resumeContext);

        // 1. Attempt Gemini AI
        if ($this->gemini->isConfigured()) {
            try {
                $rawResponse = $this->gemini->generate($prompt, true);
                if ($rawResponse) {
                    $parsed = $this->cleanAndParseJson($rawResponse);
                    if ($parsed && !empty($parsed['topics'])) {
                        return $this->normalizeTopicsData($parsed, $roleTitle, $experienceLevel, $techStack);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini AI Interview Prep generation failed: ' . $e->getMessage());
            }
        }

        // 2. Fallback to Groq AI
        if ($this->groq) {
            try {
                $rawResponse = $this->groq->generate($prompt);
                if ($rawResponse) {
                    $parsed = $this->cleanAndParseJson($rawResponse);
                    if ($parsed && !empty($parsed['topics'])) {
                        return $this->normalizeTopicsData($parsed, $roleTitle, $experienceLevel, $techStack);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Groq AI Interview Prep generation failed: ' . $e->getMessage());
            }
        }

        // 3. Fallback to intelligent rule-based / curated technical synthesis
        return $this->generateFallbackCuratedPrep($roleTitle, $experienceLevel, $techStack);
    }

    /**
     * Build the AI prompt for generating tailored interview questions.
     */
    protected function buildPrompt(
        string $roleTitle,
        string $experienceLevel,
        string $techStack,
        ?string $resumeContext
    ): string {
        $cleanResume = $resumeContext ? mb_substr(strip_tags($resumeContext), 0, 2000) : 'None provided';

        return <<<PROMPT
You are an Elite Technical Interviewer, Hiring Manager, and Principal Software Architect at a top-tier tech company.
Generate an in-depth, rigorous, practical Technical Interview Preparation Guide for a candidate.

Target Candidate Parameters:
- Target Role: "{$roleTitle}"
- Experience Level: "{$experienceLevel}"
- Tech Stack & Languages: "{$techStack}"
- Candidate Resume Context: "{$cleanResume}"

Requirements:
1. Break down the tech stack into 3 to 5 logical topics or technologies (e.g., Core Language & Runtime, Framework & Architecture, Database & Optimization, System Design & Concurrency, Real-world Debugging/Troubleshooting).
2. For each topic, produce 3 to 4 high-yield, realistic interview questions appropriate for the specified experience level ("{$experienceLevel}").
   - For Junior/Fresher: focus on language fundamentals, lifecycle, syntax nuances, common pitfalls, and foundational algorithms/data structures.
   - For Mid-Level: focus on performance tuning, database transactions, design patterns, testing, error handling, and component architecture.
   - For Senior/Lead: focus on distributed systems, trade-offs, scalability, microservices, concurrency, high availability, resilience, and architectural decisions.
3. For each question provide:
   - "id": A unique short slug string (e.g. "q_1", "q_2").
   - "difficulty": "Easy", "Medium", or "Hard".
   - "question": The exact interview question as asked by top tech interviewers.
   - "answer": A comprehensive, step-by-step model answer explaining the underlying principles and best practices.
   - "key_points": An array of 3 to 5 crucial talking points or keywords the candidate must mention verbally during their interview.
   - "code_snippet": A clean, concise code example, schema snippet, or terminal command illustrating the solution (or null if not applicable).

Format your output strictly as a valid JSON object matching this schema:
{
  "role_title": "{$roleTitle}",
  "experience_level": "{$experienceLevel}",
  "tech_stack": "{$techStack}",
  "summary": "Concise 2-sentence advice for cracking this specific role and seniority level.",
  "topics": [
    {
      "topic_name": "Topic Name Here (e.g. Core Framework & Architecture)",
      "description": "Short summary of what this topic covers",
      "questions": [
        {
          "id": "topic1_q1",
          "difficulty": "Medium",
          "question": "Question text here?",
          "answer": "Detailed model answer text...",
          "key_points": [
            "Key talking point 1",
            "Key talking point 2",
            "Key talking point 3"
          ],
          "code_snippet": "// sample code or null"
        }
      ]
    }
  ]
}

Return ONLY the raw JSON object. Do not include markdown code block formatting like ```json.
PROMPT;
    }

    /**
     * Clean and parse raw JSON from model responses.
     */
    protected function cleanAndParseJson(string $raw): ?array
    {
        $raw = trim($raw);

        // Remove markdown backticks if present
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $raw, $matches)) {
            $raw = $matches[1];
        }

        // Find JSON start and end brackets
        $firstBrace = strpos($raw, '{');
        $lastBrace = strrpos($raw, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $raw = substr($raw, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Normalize topics data to ensure all keys and IDs exist.
     */
    protected function normalizeTopicsData(
        array $parsed,
        string $roleTitle,
        string $experienceLevel,
        string $techStack
    ): array {
        $totalQuestions = 0;
        $normalizedTopics = [];
        $counter = 1;

        foreach ($parsed['topics'] ?? [] as $topicIndex => $topic) {
            $topicName = $topic['topic_name'] ?? ('Topic ' . ($topicIndex + 1));
            $description = $topic['description'] ?? '';
            $questions = [];

            foreach ($topic['questions'] ?? [] as $q) {
                if (empty($q['question'])) {
                    continue;
                }

                $questions[] = [
                    'id' => (string) ($q['id'] ?? ('q_' . $counter)),
                    'topic' => $topicName,
                    'difficulty' => in_array($q['difficulty'] ?? '', ['Easy', 'Medium', 'Hard']) ? $q['difficulty'] : 'Medium',
                    'question' => (string) $q['question'],
                    'answer' => (string) ($q['answer'] ?? ''),
                    'key_points' => is_array($q['key_points'] ?? null) ? array_values($q['key_points']) : [],
                    'code_snippet' => !empty($q['code_snippet']) ? (string) $q['code_snippet'] : null,
                ];

                $totalQuestions++;
                $counter++;
            }

            if (!empty($questions)) {
                $normalizedTopics[] = [
                    'topic_name' => $topicName,
                    'description' => $description,
                    'questions' => $questions,
                ];
            }
        }

        return [
            'role_title' => $roleTitle,
            'experience_level' => $experienceLevel,
            'tech_stack' => $techStack,
            'summary' => $parsed['summary'] ?? "Curated technical interview preparation questions for {$roleTitle} ({$experienceLevel}).",
            'total_questions' => $totalQuestions,
            'topics' => $normalizedTopics,
        ];
    }

    /**
     * High-quality deterministic fallback synthesis when AI APIs are unreachable.
     */
    protected function generateFallbackCuratedPrep(
        string $roleTitle,
        string $experienceLevel,
        string $techStack
    ): array {
        $techItems = array_filter(array_map('trim', explode(',', $techStack)));
        if (empty($techItems)) {
            $techItems = ['Core Principles', 'Architecture', 'Database', 'Best Practices'];
        }

        $topics = [];
        $counter = 1;

        foreach (array_slice($techItems, 0, 4) as $index => $tech) {
            $qList = [];

            // Question 1: Core concepts
            $qList[] = [
                'id' => 'q_' . $counter++,
                'topic' => $tech,
                'difficulty' => str_contains(strtolower($experienceLevel), 'senior') ? 'Medium' : 'Easy',
                'question' => "What are the core design principles and architectural lifecycle of {$tech} in production environments?",
                'answer' => "In production environments, {$tech} requires careful attention to state isolation, lifecycle initialization, configuration management, and deterministic error handling. Interviewers expect candidates to demonstrate how components interact under load, how memory and database connections are pooled, and how dependencies are injected cleanly.",
                'key_points' => [
                    "Lifecycle and initialization stages",
                    "Dependency injection and modular decoupling",
                    "Configuration caching and environment segregation",
                    "Resource cleanup and graceful degradation"
                ],
                'code_snippet' => "// Best practice configuration pattern in {$tech}\n// Ensure strict typing, dependency injection, and proper error handling.",
            ];

            // Question 2: Performance & Optimization
            $qList[] = [
                'id' => 'q_' . $counter++,
                'topic' => $tech,
                'difficulty' => 'Medium',
                'question' => "How do you identify and resolve performance bottlenecks, memory leaks, and slow queries when using {$tech}?",
                'answer' => "Performance optimization in {$tech} begins with telemetry and profiling tools (such as APM agents, database slow query logs, and memory heap snapshots). Solutions typically involve query indexing, Redis/in-memory caching layers, asynchronous queue processing for heavy operations, and avoiding unbounded collections in memory.",
                'key_points' => [
                    "Profiling and APM monitoring techniques",
                    "Database query indexing and preventing N+1 queries",
                    "Multi-tier caching strategies (memory vs distributed cache)",
                    "Background worker queues for asynchronous offloading"
                ],
                'code_snippet' => "// Optimization example: Caching expensive database lookups\n\$data = Cache::remember('user_payload_' . \$id, 3600, function() use (\$id) {\n    return User::with('roles')->findOrFail(\$id);\n});",
            ];

            // Question 3: Real-World Scenario / Hard Question
            $qList[] = [
                'id' => 'q_' . $counter++,
                'topic' => $tech,
                'difficulty' => 'Hard',
                'question' => "Describe a scenario where {$tech} failed or caused concurrency issues in high-traffic conditions, and how you architected the solution.",
                'answer' => "Under high concurrency, race conditions and database deadlocks frequently occur if optimistic or pessimistic locking is not enforced. The recommended architecture introduces distributed locks (e.g., Redis Redlock), idempotent transaction handling, database row locking (`SELECT FOR UPDATE`), and rate limiting to maintain data integrity.",
                'key_points' => [
                    "Database locking (Pessimistic vs Optimistic Locking)",
                    "Idempotency tokens for preventing duplicate transactions",
                    "Circuit breakers and graceful fallback strategies",
                    "Horizontal scaling vs vertical resource allocation"
                ],
                'code_snippet' => "// Concurrency safety via atomic locks\n\$lock = Cache::lock('process-payment-' . \$orderId, 10);\nif (\$lock->get()) {\n    try {\n        // Execute critical payment logic\n    } finally {\n        \$lock->release();\n    }\n}",
            ];

            $topics[] = [
                'topic_name' => "{$tech} Fundamentals & Deep Dive",
                'description' => "Key concepts, performance considerations, and real-world scenarios for {$tech}",
                'questions' => $qList,
            ];
        }

        $totalQuestions = count($topics) * 3;

        return [
            'role_title' => $roleTitle,
            'experience_level' => $experienceLevel,
            'tech_stack' => $techStack,
            'summary' => "Curated technical interview guide for {$roleTitle} ({$experienceLevel}) focusing on {$techStack}.",
            'total_questions' => $totalQuestions,
            'topics' => $topics,
        ];
    }
}
