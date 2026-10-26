<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Candidate',
            'email' => 'john@candidate.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
        $this->assertDatabaseHas('users', ['email' => 'john@candidate.com', 'role' => 'user']);
    }

    public function test_company_can_register_with_profile(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'HR Manager',
            'email' => 'hr@techcorp.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'company',
            'company_name' => 'TechCorp Global',
            'company_website' => 'https://techcorp.com',
            'company_industry' => 'Software & AI',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['company_name' => 'TechCorp Global']);
        $this->assertDatabaseHas('company_profiles', ['company_name' => 'TechCorp Global']);
    }

    public function test_user_can_login_and_access_protected_me_endpoint(): void
    {
        $user = User::factory()->create([
            'email' => 'alex@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        // Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'alex@example.com',
            'password' => 'secret123',
        ]);

        $loginRes->assertStatus(200);
        $token = $loginRes->json('token');
        $this->assertNotEmpty($token);

        // Access protected /me
        $meRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meRes->assertStatus(200);
        $meRes->assertJsonFragment(['email' => 'alex@example.com']);

        // Logout
        $logoutRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');
        $logoutRes->assertStatus(200);
    }

    public function test_company_can_create_and_manage_jobs_via_api(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        CompanyProfile::create(['user_id' => $company->id, 'company_name' => 'Nexus Inc']);
        $token = $company->createToken('test')->plainTextToken;

        // 1. Create job
        $createRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/company/jobs', [
                'title' => 'Mobile Flutter Developer',
                'job_type' => 'Full-time',
                'location' => 'Bengaluru, India',
                'salary_range' => '₹18 - ₹25 LPA',
                'skills' => 'Flutter, Dart, Riverpod, REST APIs',
                'description' => 'Build high performance mobile apps for iOS and Android.',
                'platforms' => [],
            ]);

        $createRes->assertStatus(201);
        $jobId = $createRes->json('job.id');
        $this->assertNotNull($jobId);

        // 2. Fetch company jobs
        $listRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/company/jobs');
        $listRes->assertStatus(200);
        $listRes->assertJsonFragment(['title' => 'Mobile Flutter Developer']);

        // 3. View single job details
        $showRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/company/jobs/' . $jobId);
        $showRes->assertStatus(200);
        $showRes->assertJsonFragment(['title' => 'Mobile Flutter Developer']);
    }

    public function test_company_can_pin_inquiry_and_reply_via_api(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $token = $company->createToken('test')->plainTextToken;

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Backend Architect',
            'job_type' => 'Full-time',
            'description' => 'Build scalable APIs.',
            'status' => 'active',
        ]);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Vikram Roy',
            'message' => 'What is the interview format?',
            'status' => 'new',
            'is_pinned' => false,
        ]);

        // 1. Toggle pin
        $pinRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/company/inquiries/' . $inquiry->id . '/pin');
        $pinRes->assertStatus(200);
        $this->assertTrue($inquiry->fresh()->is_pinned);

        // 2. Update status
        $statusRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/v1/company/inquiries/' . $inquiry->id . '/status', ['status' => 'under_review']);
        $statusRes->assertStatus(200);
        $this->assertEquals('under_review', $inquiry->fresh()->status);

        // 3. Reply to candidate
        $replyRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/company/inquiries/' . $inquiry->id . '/reply', [
                'company_reply' => 'The interview consists of 2 technical rounds and 1 cultural fit round.'
            ]);
        $replyRes->assertStatus(200);
        $this->assertEquals('replied', $inquiry->fresh()->status);
    }

    public function test_company_can_trigger_gemini_matching_and_batch_shortlist_via_api(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $token = $company->createToken('test')->plainTextToken;

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Cloud Specialist',
            'job_type' => 'Full-time',
            'description' => 'AWS and Kubernetes.',
            'status' => 'active',
        ]);

        JobInquiry::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Cloud Pro',
            'message' => 'AWS certified architect with Kubernetes.',
            'status' => 'new',
        ]);

        // 1. Trigger AI match via API
        $matchRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/company/jobs/' . $job->id . '/ai-match');
        $matchRes->assertStatus(200);
        $matchRes->assertJsonStructure(['success', 'leaderboard', 'total_ranked']);

        // 2. Fetch Leaderboard via API
        $boardRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/company/jobs/' . $job->id . '/leaderboard');
        $boardRes->assertStatus(200);
        $boardRes->assertJsonStructure(['success', 'metrics', 'leaderboard']);

        // 3. Batch shortlist top candidates
        $shortlistRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/company/jobs/' . $job->id . '/ai-shortlist-top', ['threshold' => 50]);
        $shortlistRes->assertStatus(200);
        $shortlistRes->assertJsonStructure(['success', 'shortlisted_count']);
    }

    public function test_candidate_can_browse_jobs_apply_and_fetch_templates_via_api(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Mobile Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'description' => 'Build Flutter apps.',
            'status' => 'active',
        ]);

        // 1. Public browse jobs
        $browseRes = $this->getJson('/api/v1/jobs');
        $browseRes->assertStatus(200);
        $browseRes->assertJsonFragment(['title' => 'Mobile Engineer']);

        // 2. Submit candidate application
        $applyRes = $this->postJson('/api/v1/jobs/' . $job->id . '/apply', [
            'candidate_name' => 'Anita Desai',
            'candidate_email' => 'anita@example.com',
            'message' => 'Experienced Flutter and Dart engineer.',
            'type' => 'application',
        ]);
        $applyRes->assertStatus(201);
        $this->assertDatabaseHas('job_inquiries', ['candidate_name' => 'Anita Desai']);

        // 3. Candidate views 15 templates
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('candidate_test')->plainTextToken;

        $templatesRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/candidate/templates');
        $templatesRes->assertStatus(200);
        $this->assertCount(15, $templatesRes->json('templates'));
    }
}
