<?php

namespace Tests\Feature;

use App\Models\InterviewPreparation;
use App\Models\MockInterviewSession;
use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockInterviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_start_mock_interview(): void
    {
        $response = $this->postJson(route('mock_interview.start'), [
            'role_title' => 'Laravel Backend Developer',
            'experience_level' => 'Senior',
            'tech_stack' => 'PHP, Laravel, MySQL',
        ]);

        $response->assertStatus(401);
    }

    public function test_candidate_can_start_mock_interview_session(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'Sara Connor']);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn("Hello Sara, welcome to your technical interview.\n\n**Question 1 of 5:** Walk me through your experience designing scalable REST APIs with Laravel.");

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->actingAs($user)->postJson(route('mock_interview.start'), [
            'role_title' => 'Senior Laravel Engineer',
            'experience_level' => 'Senior (5+ yrs)',
            'tech_stack' => 'Laravel, PHP, Redis, MySQL',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'session' => [
                    'role_title' => 'Senior Laravel Engineer',
                    'current_question_index' => 1,
                    'total_questions' => 5,
                    'status' => 'in_progress',
                ],
            ]);

        $sessionId = $response->json('session.id');
        $this->assertDatabaseHas('mock_interview_sessions', [
            'id' => $sessionId,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'current_question_index' => 1,
        ]);
    }

    public function test_candidate_can_submit_turn_and_receive_next_question(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'John Developer']);

        $session = MockInterviewSession::create([
            'user_id' => $user->id,
            'role_title' => 'Full Stack Engineer',
            'experience_level' => 'Mid-Level',
            'tech_stack' => 'PHP, Vue, MySQL',
            'status' => 'in_progress',
            'current_question_index' => 1,
            'total_questions' => 5,
            'conversation_history' => [
                ['role' => 'interviewer', 'content' => 'Question 1: Explain MVC.', 'question_number' => 1, 'timestamp' => now()->toISOString()]
            ],
        ]);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn("Thank you.\n\n**Question 2 of 5:** How do you handle database indexing and slow query optimization?");

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->actingAs($user)->postJson(route('mock_interview.answer', $session->id), [
            'answer' => 'MVC separates concerns into Model for data, View for presentation, and Controller for business logic.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'turn' => [
                    'status' => 'in_progress',
                    'question_number' => 2,
                    'total_questions' => 5,
                    'is_final' => false,
                ],
            ]);

        $session->refresh();
        $this->assertEquals(2, $session->current_question_index);
        $this->assertCount(3, $session->conversation_history); // Interviewer Q1, Candidate A1, Interviewer Q2
    }

    public function test_candidate_completes_interview_on_final_question_and_receives_scorecard(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'Alice Engineer']);

        $session = MockInterviewSession::create([
            'user_id' => $user->id,
            'role_title' => 'DevOps Architect',
            'experience_level' => 'Senior',
            'tech_stack' => 'Docker, Kubernetes, AWS',
            'status' => 'in_progress',
            'current_question_index' => 5, // Last question!
            'total_questions' => 5,
            'conversation_history' => [
                ['role' => 'interviewer', 'content' => 'Question 1', 'question_number' => 1, 'timestamp' => now()->toISOString()],
                ['role' => 'candidate', 'content' => 'Answer 1', 'question_number' => 1, 'timestamp' => now()->toISOString()],
                ['role' => 'interviewer', 'content' => 'Question 2', 'question_number' => 2, 'timestamp' => now()->toISOString()],
                ['role' => 'candidate', 'content' => 'Answer 2', 'question_number' => 2, 'timestamp' => now()->toISOString()],
                ['role' => 'interviewer', 'content' => 'Question 3', 'question_number' => 3, 'timestamp' => now()->toISOString()],
                ['role' => 'candidate', 'content' => 'Answer 3', 'question_number' => 3, 'timestamp' => now()->toISOString()],
                ['role' => 'interviewer', 'content' => 'Question 4', 'question_number' => 4, 'timestamp' => now()->toISOString()],
                ['role' => 'candidate', 'content' => 'Answer 4', 'question_number' => 4, 'timestamp' => now()->toISOString()],
                ['role' => 'interviewer', 'content' => 'Question 5: Describe a major incident response.', 'question_number' => 5, 'timestamp' => now()->toISOString()],
            ],
        ]);

        $scorecardJson = json_encode([
            'overall_score' => 88,
            'verdict' => 'Strong Hire',
            'readiness_level' => 'Senior',
            'summary' => 'Outstanding candidate with deep infrastructure expertise.',
            'strengths' => ['Kubernetes pod scheduling mastery', 'Strong incident response methodology'],
            'growth_areas' => ['Could mention cost optimization metrics'],
            'question_reviews' => [
                [
                    'question_number' => 5,
                    'score' => 9,
                    'topic' => 'Incident Response',
                    'strengths' => 'Calm and methodical post-mortem approach',
                    'missing' => 'None',
                    'ideal_key_points' => 'Isolation, communication, root-cause analysis',
                ]
            ],
        ]);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn($scorecardJson);

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->actingAs($user)->postJson(route('mock_interview.answer', $session->id), [
            'answer' => 'During an outage, I immediately halted deployments, checked Kubernetes crash logs, rolled back to the prior stable Helm release, and conducted a blameless post-mortem.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'turn' => [
                    'status' => 'completed',
                    'is_final' => true,
                ],
            ]);

        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->evaluation_report);
        $this->assertEquals(88, $session->evaluation_report['overall_score']);
        $this->assertEquals('Strong Hire', $session->evaluation_report['verdict']);
    }

    public function test_user_cannot_access_or_answer_another_users_interview(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $session = MockInterviewSession::create([
            'user_id' => $owner->id,
            'role_title' => 'Software Engineer',
            'experience_level' => 'Junior',
            'tech_stack' => 'PHP',
            'status' => 'in_progress',
            'current_question_index' => 1,
            'total_questions' => 5,
            'conversation_history' => [],
        ]);

        $response = $this->actingAs($otherUser)->postJson(route('mock_interview.answer', $session->id), [
            'answer' => 'Attempting unauthorized answer submission.',
        ]);

        $response->assertStatus(404);
    }

    public function test_candidate_can_fetch_mock_interview_history(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        MockInterviewSession::create([
            'user_id' => $user->id,
            'role_title' => 'React Developer',
            'experience_level' => 'Mid-Level',
            'tech_stack' => 'React, Redux',
            'status' => 'completed',
            'current_question_index' => 5,
            'total_questions' => 5,
            'conversation_history' => [],
            'evaluation_report' => [
                'overall_score' => 82,
                'verdict' => 'Hire',
            ],
        ]);

        $response = $this->actingAs($user)->getJson(route('mock_interview.history'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'role_title' => 'React Developer',
                        'overall_score' => 82,
                        'verdict' => 'Hire',
                    ]
                ],
            ]);
    }
}

