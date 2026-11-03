<?php

namespace Tests\Feature;

use App\Models\MultilingualResume;
use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultilingualResumeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function getSampleResumeJson(): string
    {
        return json_encode([
            'name' => 'John Doe',
            'job_title' => 'Full Stack Developer',
            'contact' => 'john@example.com | +1234567890 | Berlin',
            'summary' => 'Experienced software engineer with 5 years in Laravel and Vue.',
            'skills' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Docker'],
            'experience' => [
                [
                    'title' => 'Software Engineer',
                    'company' => 'Tech Corp',
                    'location' => 'Berlin',
                    'date' => '2021 - Present',
                    'description' => "Developed REST APIs with high throughput.\nMaintained CI/CD pipelines.",
                ]
            ],
            'projects' => [
                [
                    'title' => 'Cloud Portal',
                    'link' => 'https://github.com/example/portal',
                    'tech_stack' => 'Laravel, Docker',
                    'date' => '2023',
                    'description' => 'Scalable microservice portal.',
                ]
            ],
            'education' => [
                [
                    'degree' => 'B.S. in Computer Science',
                    'institution' => 'Technical University of Berlin',
                    'location' => 'Berlin',
                    'gpa' => '3.8',
                    'date' => '2020',
                ]
            ],
            'certifications' => ['AWS Certified Developer'],
        ]);
    }

    protected function getSampleGermanResumeJson(): string
    {
        return json_encode([
            'name' => 'John Doe',
            'job_title' => 'Full-Stack-Entwickler',
            'contact' => 'john@example.com | +1234567890 | Berlin',
            'summary' => 'Erfahrener Softwareentwickler mit 5 Jahren Erfahrung in Laravel und Vue.',
            'skills' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Docker'],
            'experience' => [
                [
                    'title' => 'Softwareentwickler',
                    'company' => 'Tech Corp',
                    'location' => 'Berlin',
                    'date' => '2021 - Heute',
                    'description' => "Entwicklung von REST-APIs mit hohem Durchsatz.\nWartung von CI/CD-Pipelines.",
                ]
            ],
            'projects' => [
                [
                    'title' => 'Cloud Portal',
                    'link' => 'https://github.com/example/portal',
                    'tech_stack' => 'Laravel, Docker',
                    'date' => '2023',
                    'description' => 'Skalierbares Microservice-Portal.',
                ]
            ],
            'education' => [
                [
                    'degree' => 'Bachelor in Informatik',
                    'institution' => 'Technische Universität Berlin',
                    'location' => 'Berlin',
                    'gpa' => '3.8',
                    'date' => '2020',
                ]
            ],
            'certifications' => ['AWS Certified Developer'],
        ]);
    }

    public function test_guest_cannot_access_multilingual_resumes(): void
    {
        $response = $this->get(route('multilingual_resumes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_candidate_can_view_multilingual_resumes_hub(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'resume_json' => $this->getSampleResumeJson(),
            'resume_text' => 'Sample resume text...',
        ]);

        $response = $this->actingAs($user)->get(route('multilingual_resumes.index'));

        $response->assertStatus(200);
        $response->assertSee('AI Multilingual Resume Studio');
        $response->assertSee('German');
        $response->assertSee('Spanish');
        $response->assertSee('French');
        $response->assertSee('Japanese');
    }

    public function test_candidate_can_generate_multilingual_resume(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'resume_json' => $this->getSampleResumeJson(),
            'resume_text' => 'Sample resume text...',
        ]);

        $mockGroq = $this->createMock(GroqAiService::class);
        $mockGroq->expects($this->once())
            ->method('generate')
            ->willReturn($this->getSampleGermanResumeJson());

        $this->app->instance(GroqAiService::class, $mockGroq);

        $response = $this->actingAs($user)->post(route('multilingual_resumes.generate'), [
            'languages' => ['German'],
            'template_id' => 2,
            'target_role' => 'Senior Backend Engineer',
        ]);

        $response->assertRedirect(route('multilingual_resumes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('multilingual_resumes', [
            'user_id' => $user->id,
            'language_name' => 'German',
            'language_code' => 'de',
            'template_id' => 2,
            'target_role' => 'Senior Backend Engineer',
        ]);
    }

    public function test_candidate_can_preview_multilingual_resume(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $resume = MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'German',
            'language_code' => 'de',
            'language_flag' => '🇩🇪',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleGermanResumeJson(), true),
        ]);

        $response = $this->actingAs($user)->getJson(route('multilingual_resumes.preview', $resume->id));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'resume' => [
                'id' => $resume->id,
                'language_name' => 'German',
            ],
        ]);
        $this->assertNotEmpty($response->json('html'));
    }

    public function test_candidate_can_download_multilingual_resume_docx(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'John Doe']);
        $resume = MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'German',
            'language_code' => 'de',
            'language_flag' => '🇩🇪',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleGermanResumeJson(), true),
        ]);

        $response = $this->actingAs($user)->get(route('multilingual_resumes.download', $resume->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('John_Doe_German_Resume.docx', $response->headers->get('content-disposition'));
    }

    public function test_candidate_cannot_access_another_candidates_translated_resume(): void
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $resume = MultilingualResume::create([
            'user_id' => $user1->id,
            'language_name' => 'Spanish',
            'language_code' => 'es',
            'language_flag' => '🇪🇸',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleResumeJson(), true),
        ]);

        $response = $this->actingAs($user2)->get(route('multilingual_resumes.download', $resume->id));
        $response->assertStatus(404);
    }

    public function test_candidate_can_delete_multilingual_resume(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $resume = MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => 'French',
            'language_code' => 'fr',
            'language_flag' => '🇫🇷',
            'template_id' => 1,
            'resume_data' => json_decode($this->getSampleResumeJson(), true),
        ]);

        $response = $this->actingAs($user)->delete(route('multilingual_resumes.destroy', $resume->id));

        $response->assertRedirect(route('multilingual_resumes.index'));
        $this->assertDatabaseMissing('multilingual_resumes', ['id' => $resume->id]);
    }
}

