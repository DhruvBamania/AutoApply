<?php

namespace Tests\Feature;

use App\Models\SavedApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChromeExtensionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_extension_api(): void
    {
        $response = $this->getJson('/api/v1/candidate/extension/status');
        $response->assertStatus(401);
    }

    public function test_candidate_can_access_extension_status(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
            'name' => 'Jane Candidate',
            'email' => 'jane.candidate@example.com',
        ]);

        Sanctum::actingAs($candidate);

        $response = $this->getJson('/api/v1/candidate/extension/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $candidate->id,
                    'name' => 'Jane Candidate',
                    'email' => 'jane.candidate@example.com',
                ],
            ]);
    }

    public function test_candidate_can_analyze_job_posting(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($candidate);

        $payload = [
            'job_title' => 'Senior Full Stack Engineer',
            'company' => 'Acme Technologies',
            'job_description' => 'We are seeking an experienced full stack developer proficient in PHP, Laravel, React, TypeScript, and MySQL. Experience with Redis, Docker, and REST APIs is required.',
        ];

        $response = $this->postJson('/api/v1/candidate/extension/analyze', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'job_title',
                    'company',
                    'match_score',
                    'extracted_keywords',
                    'matched_keywords',
                    'missing_keywords',
                    'explanation',
                    'recommendations',
                ],
            ]);

        $this->assertIsInt($response->json('data.match_score'));
        $this->assertGreaterThanOrEqual(0, $response->json('data.match_score'));
        $this->assertLessThanOrEqual(100, $response->json('data.match_score'));
    }

    public function test_candidate_can_generate_screening_question_answer(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
            'name' => 'Alex Developer',
        ]);

        Sanctum::actingAs($candidate);

        $payload = [
            'question' => 'Why are you interested in this Full Stack role at Acme Technologies?',
            'job_title' => 'Senior Full Stack Engineer',
            'job_description' => 'Building scalable web applications using PHP, Laravel, and React.',
            'tone' => 'professional',
        ];

        $response = $this->postJson('/api/v1/candidate/extension/generate-answer', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'question',
                    'answer',
                    'tone',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.answer'));
    }

    public function test_candidate_can_get_autofill_profile(): void
    {
        $candidate = User::factory()->create([
            'name' => 'Dhruv Bamania',
            'email' => 'dhruv@example.com',
            'role' => 'user',
        ]);

        Sanctum::actingAs($candidate);

        $response = $this->getJson('/api/v1/candidate/extension/autofill-profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'full_name',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ]);

        $this->assertEquals('Dhruv Bamania', $response->json('data.full_name'));
        $this->assertEquals('Dhruv', $response->json('data.first_name'));
        $this->assertEquals('Bamania', $response->json('data.last_name'));
        $this->assertEquals('dhruv@example.com', $response->json('data.email'));
    }

    public function test_candidate_can_save_and_manage_tracked_applications(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($candidate);

        // 1. Save Job
        $savePayload = [
            'job_title' => 'Backend Architect',
            'company_name' => 'Stripe',
            'job_url' => 'https://www.linkedin.com/jobs/view/99999999',
            'platform' => 'linkedin',
            'location' => 'San Francisco, CA (Remote)',
            'salary' => '$180,000 - $220,000',
            'match_score' => 88,
            'status' => 'bookmarked',
        ];

        $saveResponse = $this->postJson('/api/v1/candidate/extension/save-job', $savePayload);

        $saveResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'job_title' => 'Backend Architect',
                    'company_name' => 'Stripe',
                    'platform' => 'linkedin',
                    'status' => 'bookmarked',
                ],
            ]);

        $applicationId = $saveResponse->json('data.id');
        $this->assertDatabaseHas('saved_applications', [
            'id' => $applicationId,
            'user_id' => $candidate->id,
            'company_name' => 'Stripe',
        ]);

        // 2. List Applications
        $listResponse = $this->getJson('/api/v1/candidate/extension/applications');
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data.applications'));

        // 3. Update Status
        $updateResponse = $this->patchJson("/api/v1/candidate/extension/applications/{$applicationId}", [
            'status' => 'applied',
            'notes' => 'Applied with custom resume version 2.',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'applied',
                    'notes' => 'Applied with custom resume version 2.',
                ],
            ]);

        $this->assertDatabaseHas('saved_applications', [
            'id' => $applicationId,
            'status' => 'applied',
        ]);

        // 4. Delete Application
        $deleteResponse = $this->deleteJson("/api/v1/candidate/extension/applications/{$applicationId}");
        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('saved_applications', [
            'id' => $applicationId,
        ]);
    }

    public function test_guest_can_access_chrome_extension_download_page_and_download_zip(): void
    {
        // 1. Web Page
        $indexResponse = $this->get(route('chrome_extension.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('AutoApply AI Chrome Companion', false);
        $indexResponse->assertSee(route('chrome_extension.download'), false);

        // 2. Dynamic ZIP Download
        $downloadResponse = $this->get(route('chrome_extension.download'));
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('application/zip', $downloadResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=autoapply-ai-companion.zip', $downloadResponse->headers->get('Content-Disposition'));
    }

    public function test_candidate_can_access_web_applications_tracker(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
        ]);

        SavedApplication::create([
            'user_id' => $candidate->id,
            'job_title' => 'DevOps Specialist',
            'company_name' => 'Cloudflare',
            'job_url' => 'https://boards.greenhouse.io/cloudflare/jobs/123',
            'platform' => 'greenhouse',
            'match_score' => 92,
            'status' => 'interviewing',
        ]);

        $response = $this->actingAs($candidate)->get(route('candidate.applications.index'));

        $response->assertStatus(200);
        $response->assertSee('Application Tracker', false);
        $response->assertSee('DevOps Specialist', false);
        $response->assertSee('Cloudflare', false);
        $response->assertSee('92% Match', false);
    }
}
