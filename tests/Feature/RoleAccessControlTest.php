<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_access_user_routes_but_blocked_from_company_dashboard(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
        ]);

        // Candidate accesses /dashboard
        $response = $this->actingAs($candidate)->get('/dashboard');
        $response->assertStatus(200);

        // Candidate accesses /templates
        $templateResponse = $this->actingAs($candidate)->get('/templates');
        $templateResponse->assertStatus(200);

        // Candidate attempts to access company dashboard
        $blockedResponse = $this->actingAs($candidate)->get('/company/dashboard');
        $blockedResponse->assertRedirect('/dashboard');
        $blockedResponse->assertSessionHas('error', 'Access restricted. Company features are only accessible to verified company accounts.');
    }

    public function test_company_can_access_company_dashboard_but_blocked_from_user_routes(): void
    {
        $companyUser = User::factory()->create([
            'role' => 'company',
        ]);
        CompanyProfile::create([
            'user_id' => $companyUser->id,
            'company_name' => 'Acme Corp',
        ]);

        // Company accesses /company/dashboard
        $response = $this->actingAs($companyUser)->get('/company/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Acme Corp');

        // Company attempts to access candidate dashboard
        $blockedDash = $this->actingAs($companyUser)->get('/dashboard');
        $blockedDash->assertRedirect('/company/dashboard');
        $blockedDash->assertSessionHas('error', 'Access restricted. Candidate features are not accessible to company accounts.');

        // Company attempts to access candidate resume builder
        $blockedResume = $this->actingAs($companyUser)->get('/resume-builder');
        $blockedResume->assertRedirect('/company/dashboard');
        $blockedResume->assertSessionHas('error', 'Access restricted. Candidate features are not accessible to company accounts.');

        // Company attempts to access templates
        $blockedTemplates = $this->actingAs($companyUser)->get('/templates');
        $blockedTemplates->assertRedirect('/company/dashboard');
    }

    public function test_guest_is_redirected_to_login_from_both(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/company/dashboard')->assertRedirect('/login');
        $this->get('/resume-builder')->assertRedirect('/login');
    }

    public function test_root_path_redirects_based_on_user_role(): void
    {
        $candidate = User::factory()->create(['role' => 'user']);
        $company = User::factory()->create(['role' => 'company']);

        $this->get('/')->assertRedirect('/login');
        $this->actingAs($candidate)->get('/')->assertRedirect('/dashboard');
        $this->actingAs($company)->get('/')->assertRedirect('/company/dashboard');
    }
}
