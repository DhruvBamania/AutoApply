<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1RouteTest extends TestCase
{
    use RefreshDatabase;
    public function test_api_v1_jobs_is_accessible()
    {
        $response = $this->getJson('/api/v1/jobs');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'jobs']);
    }

    public function test_direct_v1_jobs_is_accessible_without_api_prefix()
    {
        $response = $this->getJson('/v1/jobs');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'jobs']);
    }

    public function test_direct_v1_auth_login_validation_works()
    {
        $response = $this->postJson('/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);
        // Should return validation error 422, NOT 404
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }
}
