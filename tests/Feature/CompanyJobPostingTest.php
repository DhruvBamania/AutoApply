<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\ConnectedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CompanyJobPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_view_job_posting_pages(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Tech Innovations Corp',
        ]);

        $responseIndex = $this->actingAs($companyUser)->get('/company/jobs');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Multi-Platform Job Postings');

        $responseCreate = $this->actingAs($companyUser)->get('/company/jobs/create');
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Post a New Job Role');
        $responseCreate->assertSee('LinkedIn');
        $responseCreate->assertSee('Naukri');
        $responseCreate->assertSee('Indeed');
    }

    public function test_company_can_post_job_to_connected_platforms_in_one_click(): void
    {
        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response(['sub' => 'xyz123'], 200),
            'https://api.linkedin.com/v2/ugcPosts' => Http::response(['id' => 'urn:li:share:123'], 201),
            'https://login.naukri.com/v1/login' => Http::response(['status' => 'success'], 200),
            'https://secure.indeed.com/api/v1/employer/auth' => Http::response(['status' => 'success'], 200),
        ]);

        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Apex Cloud Inc.',
            'location' => 'Remote',
        ]);

        // Connect all 3 platforms
        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'linkedin',
            'connection_type' => 'oauth',
            'account_identifier' => 'recruiter@apexcloud.com',
            'access_token' => encrypt('mock_linkedin_token'),
        ]);

        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'naukri',
            'connection_type' => 'credentials',
            'account_identifier' => 'naukri_recruiter_user',
            'password' => encrypt('secret_naukri_pass'),
        ]);

        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'indeed',
            'connection_type' => 'credentials',
            'account_identifier' => 'indeed_employer@apexcloud.com',
            'password' => encrypt('secret_indeed_pass'),
        ]);

        $postData = [
            'title' => 'Senior Backend Laravel Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote, US',
            'salary_range' => '$120,000 - $150,000',
            'experience_required' => '4+ years',
            'skills' => 'PHP 8.2, Laravel 11, MySQL, Redis, AWS',
            'description' => 'We are seeking an experienced Laravel engineer to lead API development and database optimization.',
            'platforms' => ['linkedin', 'naukri', 'indeed'],
        ];

        $response = $this->actingAs($companyUser)->post('/company/jobs', $postData);

        $response->assertRedirect('/company/jobs');
        $response->assertSessionHas('success');

        $job = JobPosting::where('title', 'Senior Backend Laravel Engineer')->first();
        $this->assertNotNull($job);
        $this->assertEquals($companyUser->id, $job->user_id);
        $this->assertEquals('active', $job->status);

        // Verify all 3 platforms were successfully posted to
        $this->assertEquals('posted', $job->linkedin_status);
        $this->assertNotNull($job->linkedin_posted_at);
        $this->assertNull($job->linkedin_error);

        $this->assertEquals('posted', $job->naukri_status);
        $this->assertNotNull($job->naukri_posted_at);
        $this->assertNull($job->naukri_error);

        $this->assertEquals('posted', $job->indeed_status);
        $this->assertNotNull($job->indeed_posted_at);
        $this->assertNull($job->indeed_error);
    }

    public function test_unconnected_platforms_fail_with_clear_error_message(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Zero Integrations Corp',
        ]);

        // No connected accounts added for this company!

        $postData = [
            'title' => 'Lead DevOps Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'description' => 'Manage Kubernetes and AWS infrastructure.',
            'platforms' => ['linkedin', 'naukri', 'indeed'],
        ];

        $response = $this->actingAs($companyUser)->post('/company/jobs', $postData);

        $job = JobPosting::where('title', 'Lead DevOps Engineer')->first();
        $this->assertNotNull($job);

        // All platforms fail and record specific error messages
        $this->assertEquals('failed', $job->linkedin_status);
        $this->assertStringContainsString('LinkedIn is not connected', $job->linkedin_error);

        $this->assertEquals('failed', $job->naukri_status);
        $this->assertStringContainsString('Naukri account is not connected', $job->naukri_error);

        $this->assertEquals('failed', $job->indeed_status);
        $this->assertStringContainsString('Indeed account is not connected', $job->indeed_error);

        // Redirects to show page with warning message
        $response->assertRedirect('/company/jobs/' . $job->id);
        $response->assertSessionHas('error');
    }

    public function test_company_can_connect_and_disconnect_platforms_in_integrations(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);

        // View integrations page
        $resView = $this->actingAs($companyUser)->get('/company/integrations');
        $resView->assertStatus(200);
        $resView->assertSee('Platform & Recruiter Integrations');

        // Store Naukri credentials
        $resNaukri = $this->actingAs($companyUser)->post('/integrations/naukri', [
            'connection_type' => 'credentials',
            'account_identifier' => 'recruiter_john@company.com',
            'password' => 'secret_pass_123',
        ]);
        $resNaukri->assertSessionHas('success');
        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $companyUser->id,
            'platform' => 'naukri',
            'account_identifier' => 'recruiter_john@company.com',
        ]);

        // Disconnect Naukri
        $resDisconnect = $this->actingAs($companyUser)->delete('/integrations/naukri');
        $resDisconnect->assertSessionHas('success');
        $this->assertDatabaseMissing('connected_accounts', [
            'user_id' => $companyUser->id,
            'platform' => 'naukri',
        ]);
    }

    public function test_indeed_ats_xml_feed_generates_valid_xml(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Syndication Tech',
        ]);

        JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Software Architect',
            'job_type' => 'Full-time',
            'location' => 'Bengaluru, India',
            'salary_range' => '₹40 - 55 LPA',
            'description' => 'Architect scalable cloud microservices.',
            'status' => 'active',
        ]);

        $feedResponse = $this->get('/feed/' . $companyUser->id . '/indeed.xml');
        $feedResponse->assertStatus(200);
        $feedResponse->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $feedResponse->assertSee('<title>Software Architect</title>', false);
        $feedResponse->assertSee('<company>Syndication Tech</company>', false);
    }

    public function test_candidate_is_blocked_from_company_job_routes(): void
    {
        $candidate = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($candidate)->get('/company/jobs');
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');

        $responseCreate = $this->actingAs($candidate)->get('/company/jobs/create');
        $responseCreate->assertRedirect('/dashboard');

        $responseStore = $this->actingAs($candidate)->post('/company/jobs', [
            'title' => 'Unauthorized Post',
            'job_type' => 'Full-time',
            'description' => 'test description',
        ]);
        $responseStore->assertRedirect('/dashboard');
    }

    public function test_company_can_repost_and_delete_job(): void
    {
        Http::fake([
            'https://login.naukri.com/v1/login' => Http::response(['status' => 'success'], 200),
        ]);

        $companyUser = User::factory()->create(['role' => 'company']);
        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'naukri',
            'connection_type' => 'credentials',
            'account_identifier' => 'recruiter@company.com',
            'password' => encrypt('password'),
        ]);

        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Frontend React Engineer',
            'job_type' => 'Full-time',
            'description' => 'Build reactive dashboards',
            'naukri_status' => 'pending',
        ]);

        // 1-Click Repost to naukri
        $repostResponse = $this->actingAs($companyUser)->post("/company/jobs/{$job->id}/repost", [
            'platforms' => ['naukri'],
        ]);
        $repostResponse->assertSessionHas('success');

        $job->refresh();
        $this->assertEquals('posted', $job->naukri_status);

        // Delete Job
        $deleteResponse = $this->actingAs($companyUser)->delete("/company/jobs/{$job->id}");
        $deleteResponse->assertRedirect('/company/jobs');
        $this->assertDatabaseMissing('job_postings', ['id' => $job->id]);
    }

    public function test_company_can_post_job_with_image_attachment(): void
    {
        Storage::fake('public');

        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response(['sub' => 'xyz123'], 200),
            'https://api.linkedin.com/v2/assets?action=registerUpload' => Http::response([
                'value' => [
                    'asset' => 'urn:li:digitalmediaAsset:IMAGE123',
                    'uploadMechanism' => [
                        'com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest' => [
                            'uploadUrl' => 'https://api.linkedin.com/mediaUpload/abc',
                        ],
                    ],
                ],
            ], 200),
            'https://api.linkedin.com/mediaUpload/abc' => Http::response('', 200),
            'https://api.linkedin.com/v2/ugcPosts' => Http::response(['id' => 'urn:li:share:123'], 201),
        ]);

        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Visual Tech Corp',
        ]);

        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'linkedin',
            'connection_type' => 'oauth',
            'account_identifier' => 'recruiter@visualtech.com',
            'access_token' => encrypt('mock_linkedin_token'),
        ]);

        $fakeImage = UploadedFile::fake()->image('hiring_flyer.png', 800, 600);

        $postData = [
            'title' => 'Senior UI/UX Designer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'description' => 'Design high converting dashboards.',
            'image' => $fakeImage,
            'platforms' => ['linkedin'],
        ];

        $response = $this->actingAs($companyUser)->post('/company/jobs', $postData);
        $response->assertRedirect('/company/jobs');
        $response->assertSessionHas('success');

        $job = JobPosting::where('title', 'Senior UI/UX Designer')->first();
        $this->assertNotNull($job);
        $this->assertNotNull($job->image_path);
        Storage::disk('public')->assertExists($job->image_path);
        $this->assertEquals('posted', $job->linkedin_status);

        // Verify image appears on show page
        $showResponse = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee($job->image_path);

        // Verify Indeed ATS XML feed includes the imageurl
        $feedResponse = $this->get('/feed/' . $companyUser->id . '/indeed.xml');
        $feedResponse->assertStatus(200);
        $feedResponse->assertSee('<imageurl>', false);

        // Delete job cleans up image
        $imagePath = $job->image_path;
        $this->actingAs($companyUser)->delete('/company/jobs/' . $job->id);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_company_can_post_in_sandbox_mode_and_seed_inquiries_without_accounts(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Sandbox Tech Labs',
        ]);

        // Connect platforms via sandbox mode
        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'linkedin',
            'connection_type' => 'sandbox',
            'account_identifier' => 'LinkedIn Sandbox',
        ]);

        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'naukri',
            'connection_type' => 'sandbox',
            'account_identifier' => 'Naukri Sandbox',
        ]);

        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'indeed',
            'connection_type' => 'sandbox',
            'account_identifier' => 'Indeed Sandbox',
        ]);

        // Post job to all 3 platforms in sandbox mode (no external APIs required)
        $postData = [
            'title' => 'Sandbox Test Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'salary_range' => '$80,000 - $110,000',
            'description' => 'Test job posting in sandbox environment without live employer accounts.',
            'platforms' => ['linkedin', 'naukri', 'indeed'],
        ];

        $response = $this->actingAs($companyUser)->post('/company/jobs', $postData);
        $response->assertRedirect('/company/jobs');

        $job = JobPosting::where('title', 'Sandbox Test Engineer')->first();
        $this->assertNotNull($job);
        $this->assertEquals('posted', $job->linkedin_status);
        $this->assertEquals('posted', $job->naukri_status);
        $this->assertEquals('posted', $job->indeed_status);
        $this->assertNotNull($job->linkedin_post_id);

        // Seed demo comments and queries for this job role
        $seedResponse = $this->actingAs($companyUser)->post('/company/inquiries/seed-demo', ['job_id' => $job->id]);
        $seedResponse->assertSessionHas('success');

        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'comment',
        ]);
        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'platform' => 'naukri',
            'type' => 'application',
        ]);
        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'platform' => 'indeed',
            'type' => 'query',
        ]);

        // Verify inquiries hub displays them role wise
        $inquiriesPage = $this->actingAs($companyUser)->get('/company/inquiries');
        $inquiriesPage->assertStatus(200);
        $inquiriesPage->assertSee('Sandbox Test Engineer');
        $inquiriesPage->assertSee('Priya Sharma');
        $inquiriesPage->assertSee('Rahul Verma');
        $inquiriesPage->assertSee('Ananya Patel');
    }
}
