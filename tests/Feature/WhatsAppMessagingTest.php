<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\HeadhunterMatch;
use App\Models\JobInquiry;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Services\TwilioWhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WhatsAppMessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $companyUser;
    private CompanyProfile $profile;
    private JobPosting $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyUser = User::factory()->create([
            'role' => 'company',
            'name' => 'Acme Technologies',
            'email' => 'hiring@acme.tech',
        ]);

        $this->profile = CompanyProfile::create([
            'user_id' => $this->companyUser->id,
            'company_name' => 'Acme Technologies',
            'whatsapp_number' => '+14155552671',
        ]);

        $this->job = JobPosting::create([
            'user_id' => $this->companyUser->id,
            'title' => 'Senior Cloud Architect',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'description' => 'Architect cloud distributed infrastructure.',
            'status' => 'active',
            'whatsapp_number' => '+14155552671',
        ]);
    }

    public function test_candidate_can_submit_whatsapp_inquiry_on_web(): void
    {
        $response = $this->postJson("/jobs/{$this->job->id}/whatsapp-message", [
            'candidate_name' => 'Alex Rivera',
            'candidate_phone' => '+14155550199',
            'candidate_email' => 'alex@example.com',
            'message' => 'Is this cloud architect role open to candidates in South America?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verifies unified inquiry record is stored with platform = 'whatsapp'
        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $this->job->id,
            'candidate_name' => 'Alex Rivera',
            'candidate_phone' => '+14155550199',
            'platform' => 'whatsapp',
            'status' => 'new',
        ]);

        // Verifies outbound message dispatch is logged in whatsapp_messages
        $this->assertDatabaseHas('whatsapp_messages', [
            'job_posting_id' => $this->job->id,
            'direction' => 'candidate_to_company',
            'to_number' => 'whatsapp:+14155552671',
        ]);
    }

    public function test_candidate_can_submit_whatsapp_inquiry_via_api(): void
    {
        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/whatsapp-message", [
            'candidate_name' => 'David Kim',
            'candidate_phone' => '+14155559876',
            'message' => 'What tech stack are you using on Kubernetes clusters?',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('job_inquiries', [
            'job_posting_id' => $this->job->id,
            'candidate_name' => 'David Kim',
            'candidate_phone' => '+14155559876',
            'platform' => 'whatsapp',
        ]);
    }

    public function test_company_can_reply_to_candidate_via_whatsapp_web(): void
    {
        $inquiry = JobInquiry::create([
            'job_posting_id' => $this->job->id,
            'candidate_name' => 'Elena Rostova',
            'candidate_email' => 'elena@example.com',
            'candidate_phone' => '+14155554321',
            'message' => 'Can you sponsor an H1-B visa?',
            'platform' => 'whatsapp',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->companyUser)
            ->post("/company/inquiries/{$inquiry->id}/whatsapp-reply", [
                'reply_message' => 'Yes, we provide full H1-B visa sponsorship!',
            ]);

        $response->assertSessionHas('success');

        $inquiry->refresh();
        $this->assertEquals('replied', $inquiry->status);
        $this->assertEquals('Yes, we provide full H1-B visa sponsorship!', $inquiry->company_reply);

        $this->assertDatabaseHas('whatsapp_messages', [
            'job_inquiry_id' => $inquiry->id,
            'direction' => 'company_to_candidate',
            'to_number' => 'whatsapp:+14155554321',
        ]);
    }

    public function test_company_can_reply_to_candidate_via_whatsapp_api(): void
    {
        Sanctum::actingAs($this->companyUser);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $this->job->id,
            'candidate_name' => 'Maya Lin',
            'candidate_phone' => '+14155558833',
            'message' => 'Does this role require on-call rotations?',
            'platform' => 'whatsapp',
            'status' => 'new',
        ]);

        $response = $this->postJson("/api/v1/company/inquiries/{$inquiry->id}/whatsapp-reply", [
            'reply_message' => 'We have a follow-the-sun rotation with 1 week on-call per quarter.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $inquiry->refresh();
        $this->assertEquals('replied', $inquiry->status);
    }

    public function test_company_can_update_their_whatsapp_business_number_web_and_api(): void
    {
        // 1. Web test
        $responseWeb = $this->actingAs($this->companyUser)
            ->put('/company/profile/whatsapp', [
                'whatsapp_number' => '+14159990001',
            ]);
        $responseWeb->assertSessionHas('success');

        $this->profile->refresh();
        $this->assertEquals('+14159990001', $this->profile->whatsapp_number);

        // 2. Sanctum API test
        Sanctum::actingAs($this->companyUser);
        $responseApi = $this->putJson('/api/v1/company/profile/whatsapp', [
            'whatsapp_number' => '+14159998888',
        ]);

        $responseApi->assertStatus(200);
        $responseApi->assertJson([
            'success' => true,
        ]);

        $this->profile->refresh();
        $this->assertEquals('+14159998888', $this->profile->whatsapp_number);
    }

    public function test_company_can_send_headhunter_whatsapp_outreach_web_and_api(): void
    {
        $match = HeadhunterMatch::create([
            'job_posting_id' => $this->job->id,
            'candidate_name' => 'Jordan Taylor',
            'candidate_email' => 'jordan@example.com',
            'candidate_headline' => 'Principal Infrastructure Engineer',
            'source_platform' => 'linkedin',
            'pool_source' => 'web_scouted',
            'ai_match_score' => 94,
            'status' => 'sourced',
        ]);

        // 1. Web dispatch
        $responseWeb = $this->actingAs($this->companyUser)
            ->post("/company/headhunter/{$match->id}/whatsapp-outreach", [
                'candidate_phone' => '+14155551122',
                'outreach_message' => 'Hi Jordan, Acme Technologies would love to speak with you about our Cloud Architect opening!',
            ]);
        $responseWeb->assertSessionHas('success');

        $this->assertDatabaseHas('whatsapp_messages', [
            'headhunter_match_id' => $match->id,
            'direction' => 'company_to_candidate',
            'to_number' => 'whatsapp:+14155551122',
        ]);

        // 2. API dispatch
        Sanctum::actingAs($this->companyUser);
        $responseApi = $this->postJson("/api/v1/company/headhunter/{$match->id}/whatsapp-outreach", [
            'candidate_phone' => '+14155551122',
            'outreach_message' => 'Follow up on WhatsApp: we are scheduling interviews this Thursday.',
        ]);

        $responseApi->assertStatus(200);
        $responseApi->assertJson([
            'success' => true,
        ]);
    }

    public function test_twilio_service_normalizes_whatsapp_numbers(): void
    {
        $service = app(TwilioWhatsAppService::class);

        $this->assertEquals('whatsapp:+14155238886', $service->formatWhatsAppAddress('+1 (415) 523-8886'));
        $this->assertEquals('whatsapp:+919876543210', $service->formatWhatsAppAddress('+91 98765 43210'));
        $this->assertEquals('whatsapp:+14155238886', $service->formatWhatsAppAddress('whatsapp:+14155238886'));
    }
}
