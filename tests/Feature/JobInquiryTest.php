<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\JobInquiry;
use App\Models\ConnectedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_view_public_job_page_and_submit_inquiry(): void
    {
        Storage::fake('public');

        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Nexus Global Inc.',
        ]);

        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Senior Full Stack Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'description' => 'Build high-scale distributed applications.',
            'status' => 'active',
        ]);

        // 1. Candidate views public job page
        $viewResponse = $this->get('/jobs/' . $job->id . '?src=linkedin');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Senior Full Stack Engineer');
        $viewResponse->assertSee('Nexus Global Inc.');
        $viewResponse->assertSee('Have a Query or Want to Apply?');

        // 2. Candidate submits query with resume
        $fakeResume = UploadedFile::fake()->create('my_resume.pdf', 100, 'application/pdf');

        $submitResponse = $this->post('/jobs/' . $job->id . '/inquire', [
            'candidate_name' => 'Sarah Connor',
            'candidate_email' => 'sarah@example.com',
            'candidate_headline' => 'Senior Engineer @ TechCorp',
            'message' => 'What is the compensation range for candidates in Europe?',
            'type' => 'query',
            'platform' => 'linkedin',
            'resume' => $fakeResume,
        ]);

        $submitResponse->assertSessionHas('success');
        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'candidate_name' => 'Sarah Connor',
            'candidate_email' => 'sarah@example.com',
            'platform' => 'linkedin',
            'type' => 'query',
            'status' => 'new',
        ]);

        $inquiry = JobInquiry::first();
        $this->assertNotNull($inquiry->resume_path);
        Storage::disk('public')->assertExists($inquiry->resume_path);
    }

    public function test_company_can_view_inquiries_hub_role_wise_and_filter(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Innovate Corp',
        ]);

        $job1 = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Python AI Specialist',
            'job_type' => 'Full-time',
            'description' => 'Train LLMs and build agents.',
            'status' => 'active',
        ]);

        $job2 = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'React Frontend Developer',
            'job_type' => 'Full-time',
            'description' => 'Build reactive components.',
            'status' => 'active',
        ]);

        // Add inquiry for Job 1
        JobInquiry::create([
            'job_posting_id' => $job1->id,
            'platform' => 'linkedin',
            'type' => 'comment',
            'candidate_name' => 'John Doe',
            'message' => 'Is remote work permitted worldwide?',
            'status' => 'new',
        ]);

        // Add inquiry for Job 2
        JobInquiry::create([
            'job_posting_id' => $job2->id,
            'platform' => 'naukri',
            'type' => 'application',
            'candidate_name' => 'Alice Smith',
            'message' => 'Submitted application via Naukri.',
            'status' => 'under_review',
        ]);

        // Company views centralized Inquiries hub
        $response = $this->actingAs($companyUser)->get('/company/inquiries');
        $response->assertStatus(200);
        $response->assertSee('Job Comments & Candidate Inquiries', false);
        $response->assertSee('Python AI Specialist');
        $response->assertSee('React Frontend Developer');
        $response->assertSee('John Doe');
        $response->assertSee('Alice Smith');

        // Filter by Job 1
        $filteredResponse = $this->actingAs($companyUser)->get('/company/inquiries?job_id=' . $job1->id);
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('John Doe');
        $filteredResponse->assertDontSee('Alice Smith');
    }

    public function test_company_can_update_inquiry_status_and_send_reply(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'DevOps Cloud Architect',
            'job_type' => 'Full-time',
            'description' => 'Manage Terraform and AWS pipelines.',
            'status' => 'active',
        ]);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'direct',
            'type' => 'query',
            'candidate_name' => 'Bob Marley',
            'candidate_email' => 'bob@example.com',
            'message' => 'What cloud certifications are required?',
            'status' => 'new',
        ]);

        // 1. Update status
        $statusResponse = $this->actingAs($companyUser)->patch('/company/inquiries/' . $inquiry->id . '/status', [
            'status' => 'under_review',
        ]);
        $statusResponse->assertSessionHas('success');
        $this->assertEquals('under_review', $inquiry->fresh()->status);

        // 2. Reply to candidate
        $replyResponse = $this->actingAs($companyUser)->post('/company/inquiries/' . $inquiry->id . '/reply', [
            'company_reply' => 'AWS Solutions Architect Associate or higher is preferred.',
        ]);
        $replyResponse->assertSessionHas('success');

        $inquiry->refresh();
        $this->assertEquals('replied', $inquiry->status);
        $this->assertEquals('AWS Solutions Architect Associate or higher is preferred.', $inquiry->company_reply);
        $this->assertNotNull($inquiry->replied_at);
    }

    public function test_company_can_sync_linkedin_comments_via_api(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        ConnectedAccount::create([
            'user_id' => $companyUser->id,
            'platform' => 'linkedin',
            'connection_type' => 'oauth',
            'account_identifier' => 'recruiter@company.com',
            'access_token' => encrypt('fake_linkedin_token'),
        ]);

        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Golang Backend Specialist',
            'job_type' => 'Full-time',
            'description' => 'Build high-throughput microservices.',
            'status' => 'active',
            'linkedin_status' => 'posted',
            'linkedin_post_id' => 'urn:li:share:123456789',
        ]);

        Http::fake([
            'https://api.linkedin.com/v2/socialActions/*' => Http::response([
                'elements' => [
                    [
                        '$URN' => 'urn:li:comment:(urn:li:share:123456789,998877)',
                        'actor' => 'urn:li:person:user_abc123',
                        'message' => [
                            'text' => 'Great role! Are you sponsoring work visas?',
                        ],
                        'created' => [
                            'time' => 1697190000000,
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Trigger sync
        $syncResponse = $this->actingAs($companyUser)->post('/company/inquiries/sync', [
            'job_id' => $job->id,
        ]);
        $syncResponse->assertSessionHas('success');

        // Verify comment was retrieved and recorded
        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'comment',
            'external_id' => 'urn:li:comment:(urn:li:share:123456789,998877)',
            'message' => 'Great role! Are you sponsoring work visas?',
            'status' => 'new',
        ]);
    }

    public function test_normal_user_or_guest_cannot_access_company_inquiries(): void
    {
        $candidate = User::factory()->create(['role' => 'user']);

        // Guest redirect to login
        $this->get('/company/inquiries')->assertRedirect('/login');

        // Candidate redirect to candidate dashboard
        $this->actingAs($candidate)->get('/company/inquiries')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('error');
    }

    public function test_job_details_page_displays_role_inquiries(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Solutions Architect',
            'job_type' => 'Full-time',
            'description' => 'Lead architecture designs.',
            'status' => 'active',
        ]);

        JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'indeed',
            'type' => 'query',
            'candidate_name' => 'Charlie Day',
            'message' => 'Is this contract-to-hire or permanent?',
            'status' => 'new',
        ]);

        $response = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $response->assertStatus(200);
        $response->assertSee('Candidate Inquiries & Platform Comments', false);
        $response->assertSee('Charlie Day');
        $response->assertSee('Is this contract-to-hire or permanent?');
    }

    public function test_company_can_manually_log_comment_under_job_role(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Senior PHP Developer',
            'job_type' => 'Full-time',
            'description' => 'Build high volume web services.',
            'status' => 'active',
        ]);

        $payload = [
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'candidate_name' => 'Dhruv Bamania',
            'candidate_headline' => 'PHP Specialist',
            'candidate_email' => 'dhruv@example.com',
            'message' => 'I am interested in this role! Is there an immediate joining bonus?',
        ];

        $response = $this->actingAs($companyUser)->post('/company/inquiries/manual', $payload);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'candidate_name' => 'Dhruv Bamania',
            'message' => 'I am interested in this role! Is there an immediate joining bonus?',
            'status' => 'new',
        ]);

        $page = $this->actingAs($companyUser)->get('/company/inquiries');
        $page->assertSee('Dhruv Bamania');
        $page->assertSee('I am interested in this role!');
    }

    public function test_postman_api_can_fetch_and_sync_inquiries(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Postman Test Corp',
        ]);

        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'API Engineer',
            'job_type' => 'Full-time',
            'description' => 'Build high-performance REST APIs.',
            'status' => 'active',
        ]);

        JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'comment',
            'candidate_name' => 'Alice Test',
            'message' => 'Is this opening still available?',
            'status' => 'new',
        ]);

        // 1. GET /api/test-inquiries
        $getResponse = $this->getJson('/api/test-inquiries');
        $getResponse->assertStatus(200);
        $getResponse->assertJsonFragment(['job_title' => 'API Engineer']);
        $getResponse->assertJsonFragment(['candidate_name' => 'Alice Test']);

        // 2. POST /api/test-inquiries-sync
        $syncResponse = $this->postJson('/api/test-inquiries-sync', ['job_id' => $job->id]);
        $syncResponse->assertStatus(200);
        $syncResponse->assertJsonFragment(['success' => true]);

        // 3. POST /api/test-inquiries-seed
        $seedResponse = $this->postJson('/api/test-inquiries-seed', ['job_id' => $job->id]);
        $seedResponse->assertStatus(200);
        $seedResponse->assertJsonFragment(['success' => true]);
        $this->assertEquals(4, JobInquiry::where('job_posting_id', $job->id)->count());

        // 4. POST /api/test-inquiries-submit
        $submitResponse = $this->postJson('/api/test-inquiries-submit', [
            'job_id' => $job->id,
            'candidate_name' => 'Bob Builder',
            'candidate_email' => 'bob@builder.com',
            'message' => 'Applying directly via Postman API.',
            'platform' => 'indeed',
            'type' => 'application',
        ]);
        $submitResponse->assertStatus(201);
        $submitResponse->assertJsonFragment(['candidate_name' => 'Bob Builder']);

        // 5. POST /api/test-inquiries-reply
        $inquiryId = $submitResponse->json('inquiry.id');
        $replyResponse = $this->postJson('/api/test-inquiries-reply', [
            'inquiry_id' => $inquiryId,
            'company_reply' => 'Thanks for applying, Bob! We will schedule an interview.',
        ]);
        $replyResponse->assertStatus(200);
        $replyResponse->assertJsonFragment(['status' => 'replied']);
    }

    public function test_company_can_pin_and_unpin_inquiry_and_pinned_inquiry_appears_first(): void
    {
        $companyUser = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $companyUser->id,
            'title' => 'Lead Architect',
            'job_type' => 'Full-time',
            'description' => 'Architect modern cloud solutions.',
            'status' => 'active',
        ]);

        // Older inquiry (created 1 hour ago)
        $inquiry1 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'query',
            'candidate_name' => 'First Inquirer',
            'message' => 'First message asking about team structure.',
        ]);
        $inquiry1->created_at = now()->subHour();
        $inquiry1->save();

        // Newer inquiry (created 5 mins ago)
        $inquiry2 = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'naukri',
            'type' => 'comment',
            'candidate_name' => 'Second Inquirer',
            'message' => 'Second message asking about perks.',
        ]);
        $inquiry2->created_at = now()->subMinutes(5);
        $inquiry2->save();

        // By default without pinning, newer inquiry2 comes first
        $responseBefore = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $responseBefore->assertStatus(200);
        $inquiriesBefore = $responseBefore->viewData('inquiries');
        $this->assertEquals($inquiry2->id, $inquiriesBefore->first()->id);

        // 1. Company pins older inquiry1
        $pinResponse = $this->actingAs($companyUser)->post('/company/inquiries/' . $inquiry1->id . '/pin');
        $pinResponse->assertSessionHas('success');
        $this->assertTrue($inquiry1->fresh()->is_pinned);

        // Verify in Single Job View: inquiry1 is now first because it is pinned
        $responseAfterJob = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $responseAfterJob->assertStatus(200);
        $inquiriesAfter = $responseAfterJob->viewData('inquiries');
        $this->assertEquals($inquiry1->id, $inquiriesAfter->first()->id);
        $responseAfterJob->assertSee('📌 Pinned');

        // Verify in Inquiries Central Hub: inquiry1 is first under this job
        $responseAfterHub = $this->actingAs($companyUser)->get('/company/inquiries');
        $responseAfterHub->assertStatus(200);
        $grouped = $responseAfterHub->viewData('groupedInquiries');
        $this->assertEquals($inquiry1->id, $grouped->get($job->id)->first()->id);
        $responseAfterHub->assertSee('📌 Pinned Query');

        // 2. Company unpins inquiry1
        $unpinResponse = $this->actingAs($companyUser)->post('/company/inquiries/' . $inquiry1->id . '/pin');
        $unpinResponse->assertSessionHas('success');
        $this->assertFalse($inquiry1->fresh()->is_pinned);

        // Newer inquiry2 is now first again
        $responseUnpinned = $this->actingAs($companyUser)->get('/company/jobs/' . $job->id);
        $this->assertEquals($inquiry2->id, $responseUnpinned->viewData('inquiries')->first()->id);
    }

    public function test_company_cannot_pin_inquiries_of_another_company(): void
    {
        $company1 = User::factory()->create(['role' => 'company']);
        $company2 = User::factory()->create(['role' => 'company']);

        $job = JobPosting::create([
            'user_id' => $company1->id,
            'title' => 'Security Engineer',
            'job_type' => 'Full-time',
            'description' => 'Ensure zero-trust compliance.',
            'status' => 'active',
        ]);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'query',
            'candidate_name' => 'Eve Inquirer',
            'message' => 'Query for Company 1.',
        ]);

        // Company 2 attempts to pin Company 1's inquiry -> should receive 404
        $response = $this->actingAs($company2)->post('/company/inquiries/' . $inquiry->id . '/pin');
        $response->assertStatus(404);
        $this->assertFalse($inquiry->fresh()->is_pinned);
    }

    public function test_can_toggle_pin_via_postman_test_api(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'DevRel Advocate',
            'job_type' => 'Full-time',
            'description' => 'Engage with developer communities.',
            'status' => 'active',
        ]);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'comment',
            'candidate_name' => 'Postman Tester',
            'message' => 'Great initiative!',
            'is_pinned' => false,
        ]);

        // 1. Pin via Postman API
        $pinResponse = $this->postJson('/api/test-inquiries-pin', ['inquiry_id' => $inquiry->id]);
        $pinResponse->assertStatus(200);
        $pinResponse->assertJsonFragment(['success' => true]);
        $this->assertTrue($inquiry->fresh()->is_pinned);

        // 2. Unpin via Postman API
        $unpinResponse = $this->postJson('/api/test-inquiries-pin', ['inquiry_id' => $inquiry->id]);
        $unpinResponse->assertStatus(200);
        $this->assertFalse($inquiry->fresh()->is_pinned);
    }
}
