<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DeepLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeepLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_android_assetlinks_returns_valid_json_verification(): void
    {
        $response = $this->get('/.well-known/assetlinks.json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertEquals('android_app', $json[0]['target']['namespace']);
        $this->assertEquals('com.ailooping.mobile_app', $json[0]['target']['package_name']);
        $this->assertContains('delegate_permission/common.handle_all_urls', $json[0]['relation']);
        $this->assertNotEmpty($json[0]['target']['sha256_cert_fingerprints']);
    }

    public function test_apple_app_site_association_returns_valid_json(): void
    {
        $response = $this->get('/.well-known/apple-app-site-association');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $json = $response->json();
        $this->assertArrayHasKey('applinks', $json);
        $this->assertArrayHasKey('details', $json['applinks']);
    }

    public function test_universal_redirect_page_renders_for_jobs_target(): void
    {
        $response = $this->get('/open-app?target=jobs');

        $response->assertStatus(200);
        $response->assertSee('autoapply://jobs', false);
        $response->assertSee('ailooping://jobs', false);
        $response->assertSee('Opening in AutoApply App', false);
    }

    public function test_universal_redirect_page_renders_for_specific_job(): void
    {
        $response = $this->get('/open-app?target=job&id=77');

        $response->assertStatus(200);
        $response->assertSee('autoapply://jobs/77', false);
        $response->assertSee('ailooping://jobs/77', false);
    }

    public function test_universal_redirect_page_renders_for_scouted(): void
    {
        $response = $this->get('/open-app?target=scouted');

        $response->assertStatus(200);
        $response->assertSee('autoapply://scouted', false);
    }

    public function test_universal_redirect_page_renders_for_interview_prep(): void
    {
        $response = $this->get('/open-app?target=interview-prep');

        $response->assertStatus(200);
        $response->assertSee('autoapply://interview-prep', false);
    }

    public function test_universal_redirect_page_renders_with_auth_token(): void
    {
        $response = $this->get('/open-app?target=auth&token=test_sanctum_token_123&role=user');

        $response->assertStatus(200);
        $response->assertSee('autoapply://auth/login', false);
        $response->assertSee('test_sanctum_token_123', false);
        $response->assertSee('role=user', false);
    }

    public function test_deeplink_service_generates_correct_urls(): void
    {
        $jobLinks = DeepLinkService::forJob(99);
        $this->assertEquals('autoapply://jobs/99', $jobLinks['deeplink']);
        $this->assertStringContainsString('target=job', $jobLinks['universal']);
        $this->assertStringContainsString('id=99', $jobLinks['universal']);

        $scoutedLinks = DeepLinkService::forScouted();
        $this->assertEquals('autoapply://scouted', $scoutedLinks['deeplink']);

        $prepLinks = DeepLinkService::forInterviewPrep();
        $this->assertEquals('autoapply://interview-prep', $prepLinks['deeplink']);

        $inquiryLinks = DeepLinkService::forInquiries();
        $this->assertEquals('autoapply://company/inquiries', $inquiryLinks['deeplink']);
    }

    public function test_deeplink_service_generates_auth_token_link_for_user(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $authLinks = DeepLinkService::forAuth($user, 'jobs');

        $this->assertNotEmpty($authLinks['token']);
        $this->assertStringStartsWith('autoapply://auth/login?', $authLinks['deeplink']);
        $this->assertStringContainsString('token=', $authLinks['deeplink']);
        $this->assertStringContainsString('role=user', $authLinks['deeplink']);
        $this->assertStringContainsString('target=jobs', $authLinks['deeplink']);
    }

    public function test_authenticated_api_can_generate_deeplink(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'target' => 'jobs/55',
            'params' => [
                'referrer' => 'whatsapp_campaign',
            ],
        ];

        $response = $this->postJson('/api/v1/deeplink/generate', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'deeplink' => 'autoapply://jobs/55?referrer=whatsapp_campaign',
                'target' => 'jobs/55',
            ]);
    }
}
