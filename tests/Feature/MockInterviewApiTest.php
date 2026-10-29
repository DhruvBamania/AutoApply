<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MockInterviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_mobile_mock_api(): void
    {
        $response = $this->postJson('/api/v1/candidate/interview-prep/mock/start', [
            'role_title' => 'Mobile Flutter Developer',
            'experience_level' => 'Mid-Level',
            'tech_stack' => 'Flutter, Dart, Riverpod',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_candidate_can_start_mock_session_via_sanctum(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'Alex Mobile']);
        Sanctum::actingAs($user);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn("Welcome Alex.\n\n**Question 1 of 5:** How do you handle state management with Riverpod in Flutter?");

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->postJson('/api/v1/candidate/interview-prep/mock/start', [
            'role_title' => 'Flutter Mobile Engineer',
            'experience_level' => 'Mid-Level',
            'tech_stack' => 'Flutter, Dart, Riverpod',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'session' => [
                    'role_title' => 'Flutter Mobile Engineer',
                    'current_question_index' => 1,
                    'total_questions' => 5,
                    'status' => 'in_progress',
                ],
            ]);
    }
}

