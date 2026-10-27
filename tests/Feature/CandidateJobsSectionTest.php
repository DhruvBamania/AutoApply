<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CandidateJobsSectionTest extends TestCase
{
    use RefreshDatabase;

    private User $company;
    private User $candidate;
    private JobPosting $activeJob1;
    private JobPosting $activeJob2;
    private JobPosting $draftJob;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = User::factory()->create([
            'role' => 'company',
            'name' => 'Stripe Payments',
        ]);

        CompanyProfile::create([
            'user_id' => $this->company->id,
            'company_name' => 'Stripe Payments',
            'whatsapp_number' => '+14155557788',
        ]);

        $this->candidate = User::factory()->create([
            'role' => 'user',
            'name' => 'Maya Candidate',
        ]);

        $this->activeJob1 = JobPosting::create([
            'user_id' => $this->company->id,
            'title' => 'Senior Backend Laravel Engineer',
            'job_type' => 'Full-time',
            'location' => 'San Francisco, CA',
            'description' => 'Build high-scale payments APIs.',
            'skills' => 'Laravel, PHP, MySQL, Redis',
            'status' => 'active',
            'whatsapp_number' => '+14155557788',
        ]);

        $this->activeJob2 = JobPosting::create([
            'user_id' => $this->company->id,
            'title' => 'Flutter Mobile Architect',
            'job_type' => 'Remote',
            'location' => 'Remote',
            'description' => 'Architect cross-platform Flutter mobile applications.',
            'skills' => 'Flutter, Dart, Riverpod',
            'status' => 'active',
            'whatsapp_number' => '+14155557788',
        ]);

        $this->draftJob = JobPosting::create([
            'user_id' => $this->company->id,
            'title' => 'Internal Draft Role',
            'job_type' => 'Contract',
            'location' => 'Remote',
            'description' => 'This is not yet published.',
            'status' => 'draft',
        ]);
    }

    public function test_candidate_can_browse_active_jobs_section_on_web(): void
    {
        $response = $this->actingAs($this->candidate)->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee('Senior Backend Laravel Engineer');
        $response->assertSee('Flutter Mobile Architect');
        $response->assertSee('WhatsApp');
        $response->assertDontSee('Twilio WhatsApp');
        // Draft job must be hidden from candidates
        $response->assertDontSee('Internal Draft Role');
    }

    public function test_candidate_can_search_jobs_by_keyword_and_filter_by_type(): void
    {
        // Search by keyword
        $searchRes = $this->actingAs($this->candidate)->get('/jobs?search=Flutter');
        $searchRes->assertStatus(200);
        $searchRes->assertSee('Flutter Mobile Architect');
        $searchRes->assertDontSee('Senior Backend Laravel Engineer');

        // Filter by job type
        $filterRes = $this->actingAs($this->candidate)->get('/jobs?job_type=Full-time');
        $filterRes->assertStatus(200);
        $filterRes->assertSee('Senior Backend Laravel Engineer');
        $filterRes->assertDontSee('Flutter Mobile Architect');
    }

    public function test_candidate_dashboard_displays_company_job_openings(): void
    {
        $response = $this->actingAs($this->candidate)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Company Job Openings');
        $response->assertSee('Senior Backend Laravel Engineer');
        $response->assertSee('Flutter Mobile Architect');
        $response->assertSee('/jobs');
    }

    public function test_navigation_header_includes_explore_jobs_link(): void
    {
        // Authenticated candidate sees Explore Jobs in header
        $candidateRes = $this->actingAs($this->candidate)->get('/dashboard');
        $candidateRes->assertStatus(200);
        $candidateRes->assertSee('Explore Jobs');
    }

    public function test_guest_navigation_header_includes_browse_jobs(): void
    {
        // Public visitor sees Browse Jobs in header
        $guestRes = $this->get('/jobs');
        $guestRes->assertStatus(200);
        $guestRes->assertSee('Browse Jobs');
    }

    public function test_candidate_can_fetch_jobs_via_api(): void
    {
        $response = $this->getJson('/api/v1/jobs');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment(['title' => 'Senior Backend Laravel Engineer']);
        $response->assertJsonFragment(['title' => 'Flutter Mobile Architect']);
        $response->assertJsonMissing(['title' => 'Internal Draft Role']);
    }
}
