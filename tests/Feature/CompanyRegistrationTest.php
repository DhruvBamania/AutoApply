<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_registers_successfully_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'user',
            'name' => 'Alice Candidate',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->role);
        $this->assertNull($user->companyProfile);
    }

    public function test_company_registers_successfully_with_profile_and_redirects_to_company_dashboard(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'company',
            'name' => 'Bob Recruiter',
            'email' => 'recruiting@techinnovations.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Tech Innovations Corp',
            'website' => 'https://techinnovations.com',
            'location' => 'San Francisco, CA',
            'description' => 'A leading enterprise software company specializing in cloud AI solutions.',
        ]);

        $response->assertRedirect('/company/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'recruiting@techinnovations.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('company', $user->role);
        $this->assertTrue($user->isCompany());

        $profile = $user->companyProfile;
        $this->assertNotNull($profile);
        $this->assertEquals('Tech Innovations Corp', $profile->company_name);
        $this->assertEquals('https://techinnovations.com', $profile->website);
        $this->assertEquals('San Francisco, CA', $profile->location);
        $this->assertEquals('A leading enterprise software company specializing in cloud AI solutions.', $profile->description);

        // Verify the company can access company dashboard
        $dashResponse = $this->actingAs($user)->get('/company/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Tech Innovations Corp');
        $dashResponse->assertSee('San Francisco, CA');
        $dashResponse->assertSee('Company Account');
    }

    public function test_company_registration_requires_company_name(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'company',
            'name' => 'Charlie Employer',
            'email' => 'charlie@unknown.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => '', // missing company name
        ]);

        $response->assertSessionHasErrors(['company_name']);
        $this->assertGuest();
    }
}
