<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\AiChatService;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_chat_widget_is_rendered_in_global_layout(): void
    {
        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee('ai-chat-root', false);
        $response->assertSee('ai-chat-launcher', false);
        $response->assertSee('ai-chat-panel', false);
        $response->assertSee('AI Looping Assistant', false);
    }

    public function test_guest_can_access_ai_chat_context(): void
    {
        $response = $this->getJson(route('ai_chat.context'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'role' => 'guest',
                    'user_name' => 'Guest',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.suggestions'));
        $this->assertStringContainsString('Welcome to', $response->json('data.welcome_message'));
    }

    public function test_candidate_receives_personalized_ai_chat_context(): void
    {
        $candidate = User::factory()->create([
            'name' => 'John Candidate',
            'email' => 'john@candidate.test',
            'role' => 'candidate',
        ]);

        $response = $this->actingAs($candidate)->getJson(route('ai_chat.context'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'role' => 'candidate',
                    'user_name' => 'John Candidate',
                ],
            ]);

        $this->assertStringContainsString('John Candidate', $response->json('data.welcome_message'));
    }

    public function test_company_receives_personalized_ai_chat_context(): void
    {
        $companyUser = User::factory()->create([
            'name' => 'Acme HR',
            'email' => 'hr@acme.test',
            'role' => 'company',
        ]);

        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Acme Technologies',
            'industry' => 'Tech',
            'website' => 'https://acme.test',
        ]);

        $response = $this->actingAs($companyUser)->getJson(route('ai_chat.context'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'role' => 'company',
                    'user_name' => 'Acme Technologies',
                ],
            ]);

        $this->assertStringContainsString('Acme Technologies', $response->json('data.welcome_message'));
    }

    public function test_user_can_send_message_and_receive_ai_reply(): void
    {
        // Create an active job posting to verify search & context injection
        $company = User::factory()->create(['role' => 'company']);
        JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Senior React Developer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'salary_range' => '$90,000 - $120,000',
            'skills' => 'React, TypeScript, CSS',
            'description' => 'Build high-performance web applications using React.',
            'status' => 'active',
        ]);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn("Here are our open developer jobs! Check out [Senior React Developer](/jobs/1).");

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->postJson(route('ai_chat.send'), [
            'message' => 'Show me developer jobs',
            'history' => [
                ['role' => 'user', 'content' => 'Hello!'],
                ['role' => 'assistant', 'content' => 'Hi there, how can I help?'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'reply' => "Here are our open developer jobs! Check out [Senior React Developer](/jobs/1).",
            ]);

        $jobs = $response->json('jobs');
        $this->assertIsArray($jobs);
        $this->assertCount(1, $jobs);
        $this->assertEquals('Senior React Developer', $jobs[0]['title']);
    }

    public function test_chat_validates_required_message(): void
    {
        $response = $this->postJson(route('ai_chat.send'), [
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_service_provides_graceful_fallback_when_groq_fails(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Full Stack Laravel Engineer',
            'job_type' => 'Full-time',
            'location' => 'Hybrid',
            'description' => 'Exciting full stack Laravel engineering role.',
            'status' => 'active',
        ]);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn(null); // Simulates API failure / timeout

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->postJson(route('ai_chat.send'), [
            'message' => 'What roles are open?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $reply = $response->json('reply');
        $this->assertStringContainsString('Jobs Board', $reply);
        $this->assertNotEmpty($response->json('jobs'));
    }
}
