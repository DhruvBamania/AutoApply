<?php

namespace Tests\Feature;

use App\Models\HeadhunterMatch;
use App\Models\JobInquiry;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\AiHeadhunterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HeadhunterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_scout_candidates_for_job_posting(): void
    {
        Mail::fake();

        $company = User::factory()->create(['role' => 'company']);
        $candidateUser = User::factory()->create([
            'role' => 'user',
            'name' => 'Kavita Raman',
            'email' => 'kavita.raman@example.com',
            'resume_text' => 'Senior Full Stack Engineer with 6 years experience in Laravel, PHP, Flutter, REST API design, and MySQL.',
        ]);

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Senior Laravel & Mobile Architect',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'experience_required' => '5+ Years',
            'skills' => 'Laravel, PHP, Flutter, MySQL, REST API',
            'description' => 'Looking for senior architect to lead web and mobile application engineering.',
            'status' => 'active',
        ]);

        $response = $this->actingAs($company)->post(route('company.headhunter.scout', $job->id), [
            'limit' => 6,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('job_postings', [
            'id' => $job->id,
        ]);

        $this->assertNotNull($job->fresh()->last_headhunter_scout_at);

        $matches = HeadhunterMatch::where('job_posting_id', $job->id)->get();
        $this->assertNotEmpty($matches);

        // Registered platform candidate should be included
        $platformMatch = $matches->firstWhere('candidate_user_id', $candidateUser->id);
        $this->assertNotNull($platformMatch);
        $this->assertGreaterThanOrEqual(60, $platformMatch->ai_match_score);
        $this->assertNotNull($platformMatch->personalized_pitch);
    }

    public function test_company_can_send_personalized_outreach_invitation(): void
    {
        Mail::fake();

        $company = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Lead DevOps Engineer',
            'job_type' => 'Full-time',
            'skills' => 'AWS, Docker, Kubernetes',
            'description' => 'Manage CI/CD and cloud infra.',
            'status' => 'active',
        ]);

        $match = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Devon Vance',
            'candidate_email' => 'devon.vance@example.com',
            'pool_source' => 'external',
            'skills' => ['AWS', 'Docker', 'Kubernetes'],
            'ai_match_score' => 92,
            'ai_reasoning' => 'Exceptional cloud DevOps background.',
            'personalized_pitch' => 'Hi Devon, we noticed your DevOps expertise...',
            'status' => 'sourced',
        ]);

        $response = $this->actingAs($company)->post(route('company.headhunter.invite', $match->id), [
            'outreach_subject' => 'Exclusive Invitation: Lead DevOps Engineer',
            'outreach_message' => 'Customized pitch message for Devon.',
        ]);

        $response->assertRedirect();

        $freshMatch = $match->fresh();
        $this->assertEquals('invited', $freshMatch->status);
        $this->assertEquals('Exclusive Invitation: Lead DevOps Engineer', $freshMatch->outreach_subject);
        $this->assertEquals('Customized pitch message for Devon.', $freshMatch->personalized_pitch);
        $this->assertNotNull($freshMatch->invited_at);
    }

    public function test_company_can_batch_invite_top_candidates(): void
    {
        Mail::fake();

        $company = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Flutter Developer',
            'job_type' => 'Full-time',
            'skills' => 'Flutter, Dart, Riverpod',
            'description' => 'Build high performance cross-platform apps.',
            'status' => 'active',
        ]);

        $topMatch = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Rohan Patel',
            'candidate_email' => 'rohan.patel@example.com',
            'pool_source' => 'external',
            'skills' => ['Flutter', 'Dart'],
            'ai_match_score' => 88,
            'personalized_pitch' => 'Top match pitch',
            'status' => 'sourced',
        ]);

        $lowerMatch = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Maya Sen',
            'candidate_email' => 'maya.sen@example.com',
            'pool_source' => 'external',
            'skills' => ['Dart'],
            'ai_match_score' => 65,
            'personalized_pitch' => 'Lower match pitch',
            'status' => 'sourced',
        ]);

        $response = $this->actingAs($company)->post(route('company.headhunter.batch_invite', $job->id), [
            'min_score' => 80,
        ]);

        $response->assertRedirect();

        $this->assertEquals('invited', $topMatch->fresh()->status);
        $this->assertEquals('sourced', $lowerMatch->fresh()->status);
    }

    public function test_candidate_can_view_scouted_invitations(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $candidate = User::factory()->create(['role' => 'user']);

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Staff Security Engineer',
            'job_type' => 'Full-time',
            'skills' => 'Cybersecurity, AppSec',
            'description' => 'Lead security architecture.',
            'status' => 'active',
        ]);

        HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_user_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'pool_source' => 'platform',
            'skills' => ['Cybersecurity'],
            'ai_match_score' => 95,
            'personalized_pitch' => 'We invite you to connect regarding Security Engineer role.',
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $response = $this->actingAs($candidate)->get(route('candidate.headhunter.index'));

        $response->assertStatus(200);
        $response->assertSee('Scouted by Employers');
        $response->assertSee('Staff Security Engineer');
        $response->assertSee('95% Match');
    }

    public function test_candidate_accepts_invitation_and_automatically_joins_shortlist(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $candidate = User::factory()->create(['role' => 'user', 'name' => 'Zara Khan']);

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'AI Solutions Engineer',
            'job_type' => 'Full-time',
            'skills' => 'Python, LLMs, LangChain',
            'description' => 'Build generative AI solutions.',
            'status' => 'active',
        ]);

        $match = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_user_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'pool_source' => 'platform',
            'skills' => ['Python', 'LLMs'],
            'ai_match_score' => 91,
            'personalized_pitch' => 'Pitch to Zara',
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $response = $this->actingAs($candidate)->post(route('candidate.headhunter.respond', $match->id), [
            'action' => 'accepted',
            'note' => 'Delighted to connect!',
        ]);

        $response->assertRedirect();

        $freshMatch = $match->fresh();
        $this->assertEquals('accepted', $freshMatch->status);
        $this->assertNotNull($freshMatch->responded_at);

        // Verification of seamless auto-shortlisting into JobInquiry
        $inquiry = JobInquiry::where('job_posting_id', $job->id)
            ->where('candidate_email', $candidate->email)
            ->first();

        $this->assertNotNull($inquiry);
        $this->assertEquals('shortlisted', $inquiry->status);
        $this->assertEquals(91, $inquiry->ai_match_score);
        $this->assertEquals('headhunter_scout', $inquiry->type);
        $this->assertStringContainsString('Accepted AI Headhunter Invitation', $inquiry->message);
    }

    public function test_candidate_can_decline_invitation(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $candidate = User::factory()->create(['role' => 'user']);

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'QA Automation Lead',
            'job_type' => 'Full-time',
            'skills' => 'Cypress, Jest',
            'description' => 'Lead automated testing.',
            'status' => 'active',
        ]);

        $match = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_user_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'pool_source' => 'platform',
            'skills' => ['Cypress'],
            'ai_match_score' => 84,
            'personalized_pitch' => 'Pitch to candidate',
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $response = $this->actingAs($candidate)->post(route('candidate.headhunter.respond', $match->id), [
            'action' => 'declined',
        ]);

        $response->assertRedirect();
        $this->assertEquals('declined', $match->fresh()->status);
        $this->assertNotNull($match->fresh()->responded_at);
    }

    public function test_mobile_api_company_headhunter_scout_and_invite_endpoints(): void
    {
        Mail::fake();

        $company = User::factory()->create(['role' => 'company']);
        $token = $company->createToken('test-mobile-token')->plainTextToken;

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Principal Systems Architect',
            'job_type' => 'Full-time',
            'skills' => 'Golang, Distributed Systems, Kubernetes',
            'description' => 'High throughput systems.',
            'status' => 'active',
        ]);

        // 1. Scout via API
        $scoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/company/jobs/{$job->id}/headhunter/scout", ['limit' => 4]);

        $scoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. Fetch matches via API
        $matchesResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/company/jobs/{$job->id}/headhunter");

        $matchesResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $matchesData = $matchesResponse->json('data');
        $this->assertNotEmpty($matchesData);

        // 3. Send invite via API
        $firstMatchId = $matchesData[0]['id'];
        $inviteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/company/headhunter/{$firstMatchId}/invite", [
                'outreach_subject' => 'Mobile API Invitation',
                'outreach_message' => 'Invitation sent via Mobile App',
            ]);

        $inviteResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'invited');
    }

    public function test_mobile_api_candidate_headhunter_invitations_and_response_endpoints(): void
    {
        $company = User::factory()->create(['role' => 'company']);
        $candidate = User::factory()->create(['role' => 'user', 'email' => 'api.candidate@example.com']);
        $candidateToken = $candidate->createToken('candidate-token')->plainTextToken;

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Lead Backend Architect',
            'job_type' => 'Full-time',
            'skills' => 'Golang, PostgreSQL',
            'description' => 'High throughput backend.',
            'status' => 'active',
        ]);

        $candidateMatch = HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_user_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'pool_source' => 'platform',
            'skills' => ['Golang'],
            'ai_match_score' => 90,
            'personalized_pitch' => 'API pitch',
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $candidateInvitationsResponse = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->getJson('/api/v1/candidate/headhunter/invitations');

        $candidateInvitationsResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $respondResponse = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->postJson("/api/v1/candidate/headhunter/invitations/{$candidateMatch->id}/respond", [
                'action' => 'accepted',
            ]);

        $respondResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'accepted');
    }

    public function test_company_can_view_job_details_page_with_headhunter_matches(): void
    {
        $company = User::factory()->create(['role' => 'company']);

        $job = JobPosting::create([
            'user_id' => $company->id,
            'title' => 'Lead Cloud DevOps Engineer',
            'job_type' => 'Full-time',
            'location' => 'Remote',
            'experience_required' => '4+ Years',
            'skills' => 'AWS, Docker, Kubernetes, Terraform',
            'description' => 'Lead cloud infrastructure deployment.',
            'status' => 'active',
        ]);

        HeadhunterMatch::create([
            'job_posting_id' => $job->id,
            'candidate_name' => 'Ananya Iyer',
            'candidate_email' => 'ananya.iyer@example.com',
            'candidate_headline' => 'DevOps & SRE Specialist',
            'source_platform' => 'github',
            'match_score' => 92,
            'outreach_subject' => 'DevOps opportunity at our company',
            'outreach_message' => 'We would love to discuss our infrastructure.',
            'status' => 'sourced',
        ]);

        $response = $this->actingAs($company)->get(route('company.jobs.show', $job->id));

        $response->assertStatus(200);
        $response->assertSee('AI Headhunter Agent: Autonomous Talent Scout');
        $response->assertSee('Ananya Iyer');
        $response->assertSee('DevOps & SRE Specialist');
        $response->assertSee('92%');
    }
}
