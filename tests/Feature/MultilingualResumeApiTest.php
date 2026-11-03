<?php

namespace Tests\Feature;

use App\Models\MultilingualResume;
use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultilingualResumeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function getSampleResumeJson(): string
    {
        return json_encode([
            'name' => 'Jane Smith',
            'job_title' => 'Mobile Engineer',
            'contact' => 'jane@example.com | +1234567890',
            'summary' => 'Experienced Flutter engineer.',
            'skills' => ['Flutter', 'Dart', 'Riverpod'],
            'experience' => [
                [
                    'title' => 'Flutter Developer',
                    'company' => 'App Studios',
                    'location' => 'Remote',
                    'date' => '2022 - Present',
                    'description' => 'Built cross-platform iOS and Android apps.',
                ]
            ],
            'projects' => [],
            'education' => [],
            'certifications' => [],
        ]);
    }

    protected function getSampleSpanishResumeJson(): string
    {
        return json_encode([
            'name' => 'Jane Smith',
            'job_title' => 'Ingeniera Móvil',
            'contact' => 'jane@example.com | +1234567890',
            'summary' => 'Ingeniera de Flutter experimentada.',
            'skills' => ['Flutter', 'Dart', 'Riverpod'],
            'experience' => [
                [
                    'title' => 'Desarrolladora de Flutter',
                    'company' => 'App Studios',
                    'location' => 'Remoto',
                    'date' => '2022 - Presente',
                    'description' => 'Desarrollo de aplicaciones multiplataforma para iOS y Android.',
                ]
            ],
            'projects' => [],
            'education' => [],
            'certifications' => [],
        ]);
    }

    public function test_guest_cannot_access_multilingual_api(): void
    {
        $response = $this->getJson('/api/v1/candidate/resumes/multilingual');
        $response->assertStatus(401);
    }

    public function test_authenticated_candidate_can_list_multilingual_resumes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'Japanese',
            'language_code' => 'ja',
            'language_flag' => '🇯🇵',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleResumeJson(), true),
        ]);

        $response = $this->getJson('/api/v1/candidate/resumes/multilingual');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'language_name' => 'Japanese',
                'language_code' => 'ja',
            ]);
    }

    public function test_authenticated_candidate_can_translate_via_api(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'resume_json' => $this->getSampleResumeJson(),
            'resume_text' => 'Sample resume text...',
        ]);
        Sanctum::actingAs($user);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('generate')
            ->willReturn($this->getSampleSpanishResumeJson());

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->postJson('/api/v1/candidate/resumes/multilingual/translate', [
            'language' => 'Spanish',
            'template_id' => 3,
            'target_role' => 'Senior Mobile Architect',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('multilingual_resumes', [
            'user_id' => $user->id,
            'language_name' => 'Spanish',
            'language_code' => 'es',
            'template_id' => 3,
        ]);
    }

    public function test_candidate_can_view_single_translated_resume_api(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $resume = MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'French',
            'language_code' => 'fr',
            'language_flag' => '🇫🇷',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleResumeJson(), true),
        ]);

        $response = $this->getJson("/api/v1/candidate/resumes/multilingual/{$resume->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $resume->id,
                    'language_name' => 'French',
                ],
            ]);
    }

    public function test_candidate_can_delete_translated_resume_api(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $resume = MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'Italian',
            'language_code' => 'it',
            'language_flag' => '🇮🇹',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleResumeJson(), true),
        ]);

        $response = $this->deleteJson("/api/v1/candidate/resumes/multilingual/{$resume->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('multilingual_resumes', ['id' => $resume->id]);
    }
}

