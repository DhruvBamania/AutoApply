<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\JobInquiry;
use App\Services\CandidateMatcherService;
use App\Services\GeminiAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_matcher_evaluates_and_ranks_candidates_with_gemini_ai(): void
    {
        Storage::fake('public');

        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Senior Laravel Backend Architect',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'experience_required' => '5+ Years',
            'skills' => 'PHP, Laravel, MySQL, REST APIs, Redis, AWS',
            'description' => 'Architect high-scale microservices and data pipelines using Laravel, Redis, and MySQL.',
            'status' => 'active',
        ]);

        // Candidate 1: Strong Match with uploaded fake resume
        $fakeResume1 = UploadedFile::fake()->create('dev_resume.txt', 150, 'text/plain');
        $resumePath1 = Storage::disk('public')->putFile('job_resumes', $fakeResume1);

        $candidate1 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'application',
            'candidate_name' => 'Aarav Mehta',
            'candidate_email' => 'aarav.mehta@example.com',
            'candidate_headline' => 'Lead Laravel Developer | 6 Years Exp',
            'message' => 'Built multi-tenant fintech SaaS with Laravel and Redis.',
            'resume_path' => $resumePath1,
            'status' => 'new',
        ]);

        // Candidate 2: Moderate Match
        $candidate2 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'naukri',
            'type' => 'application',
            'candidate_name' => 'Pooja Iyer',
            'candidate_email' => 'pooja.iyer@example.com',
            'candidate_headline' => 'Junior PHP Developer | 1.5 Years Exp',
            'message' => 'Familiar with core PHP and basic REST APIs.',
            'status' => 'new',
        ]);

        // Mock Gemini Generative Language API
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        [
                                            'candidate_id' => $candidate1->id,
                                            'match_score' => 93,
                                            'experience_score' => 95,
                                            'skills_score' => 92,
                                            'projects_score' => 90,
                                            'recommendation' => 'Strong Match',
                                            'summary' => 'Extensive 6+ years building microservices with Laravel, Redis, and high-throughput APIs.',
                                            'matched_skills' => ['PHP', 'Laravel', 'Redis', 'MySQL', 'REST APIs'],
                                            'missing_skills' => ['AWS'],
                                        ],
                                        [
                                            'candidate_id' => $candidate2->id,
                                            'match_score' => 58,
                                            'experience_score' => 50,
                                            'skills_score' => 65,
                                            'projects_score' => 55,
                                            'recommendation' => 'Moderate Match',
                                            'summary' => 'Junior experience; possesses foundational PHP knowledge but lacks enterprise architectural depth.',
                                            'matched_skills' => ['PHP', 'REST APIs'],
                                            'missing_skills' => ['Laravel', 'Redis', 'AWS'],
                                        ]
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        // Set fake Gemini API key to trigger Gemini path
        config(['services.gemini.key' => 'fake_gemini_api_key']);
        config(['services.gemini.model' => 'gemini-1.5-flash']);

        $geminiService = new GeminiAiService('fake_gemini_api_key', 'gemini-1.5-flash');
        $matcher = new CandidateMatcherService($geminiService);

        $ranked = $matcher->matchAndRankCandidates($job);

        $this->assertCount(2, $ranked);

        // Candidate 1 should be ranked #1
        $fresh1 = $candidate1->fresh();
        $this->assertEquals(1, $fresh1->ai_rank);
        $this->assertEquals(93, $fresh1->ai_match_score);
        $this->assertEquals(95, $fresh1->ai_experience_score);
        $this->assertEquals(92, $fresh1->ai_skills_score);
        $this->assertEquals('Strong Match', $fresh1->ai_recommendation);
        $this->assertContains('Laravel', $fresh1->ai_matched_skills);

        // Candidate 2 should be ranked #2
        $fresh2 = $candidate2->fresh();
        $this->assertEquals(2, $fresh2->ai_rank);
        $this->assertEquals(58, $fresh2->ai_match_score);
        $this->assertEquals('Moderate Match', $fresh2->ai_recommendation);

        // Job posting last_ai_match_at should be recorded
        $this->assertNotNull($job->fresh()->last_ai_match_at);
    }

    public function test_company_can_trigger_ai_match_and_view_ranked_leaderboard_in_ui(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Lead DevOps Engineer',
            'job_type' => 'Full-time',
            'description' => 'Kubernetes and Terraform expert.',
            'skills' => 'Kubernetes, Docker, Terraform, AWS',
            'status' => 'active',
        ]);

        $cand = JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Karan Singh',
            'candidate_email' => 'karan@example.com',
            'candidate_headline' => 'DevOps Specialist @ CloudTech',
            'message' => '5 years managing Kubernetes clusters and CI/CD pipelines.',
            'status' => 'new',
        ]);

        // Trigger AI matching via Web Route
        $response = $this->actingAs($companyUser)->post('/company/jobs/' . $job->id . '/ai-match');
        $response->assertSessionHas('success');

        // Check Job Details page renders the leaderboard
        $viewResponse = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Gemini AI Candidate Match & Rank', false);
        $viewResponse->assertSee('Rank #1', false);
        $viewResponse->assertSee('Karan Singh', false);
        $viewResponse->assertSee('Experience Alignment', false);
        $viewResponse->assertSee('Key Skills Match', false);
        $viewResponse->assertSee('Projects & Impact', false);

        $this->assertNotNull($cand->fresh()->ai_match_score);
    }

    public function test_company_can_batch_shortlist_top_candidates(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Full Stack Architect',
            'job_type' => 'Full-time',
            'description' => 'Architect modern full stack systems.',
            'status' => 'active',
        ]);

        $cand1 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'High Scorer',
            'message' => 'Applied',
            'ai_match_score' => 88,
            'ai_rank' => 1,
            'status' => 'new',
        ]);

        $cand2 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Low Scorer',
            'message' => 'Applied',
            'ai_match_score' => 45,
            'ai_rank' => 2,
            'status' => 'new',
        ]);

        // Batch shortlist candidates with score >= 75
        $response = $this->actingAs($companyUser)->post('/company/jobs/' . $job->id . '/ai-shortlist-top', [
            'threshold' => 75
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('shortlisted', $cand1->fresh()->status);
        $this->assertEquals('new', $cand2->fresh()->status);
    }

    public function test_other_company_cannot_trigger_ai_match_on_another_company_job(): void
    {
        $company1 = User::factory()->create(['role' => 'company']);
        $company2 = User::factory()->create(['role' => 'company']);

        $job = JobPosting::create([
            'user_id' => $company1->id,
            'title' => 'Secret AI Engineer',
            'job_type' => 'Full-time',
            'description' => 'Build advanced AI agents.',
            'status' => 'active',
        ]);

        $response = $this->actingAs($company2)->post('/company/jobs/' . $job->id . '/ai-match');
        $response->assertStatus(404);
    }

    public function test_postman_api_returns_ai_candidate_matching_json(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'AI Prompt Engineer',
            'job_type' => 'Full-time',
            'skills' => 'Python, LLMs, LangChain',
            'description' => 'Build advanced AI agent workflows.',
            'status' => 'active',
        ]);

        JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Sam Altman Clone',
            'candidate_email' => 'sam@example.com',
            'candidate_headline' => 'AI Researcher',
            'message' => 'Experience with LangChain, Python, and fine-tuning LLMs.',
            'status' => 'new',
        ]);

        $response = $this->postJson('/api/test-candidate-matching', ['job_id' => $job->id]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $response->assertJsonFragment(['candidate_id' => JobInquiry::first()->id]);
        $response->assertJsonStructure([
            'success',
            'message',
            'job_id',
            'job_title',
            'total_evaluated',
            'top_matches_count',
            'ranked_candidates' => [
                [
                    'rank',
                    'candidate_id',
                    'name',
                    'match_score',
                    'experience_score',
                    'skills_score',
                    'projects_score',
                    'recommendation',
                    'summary',
                    'status',
                ]
            ]
        ]);
    }
}
