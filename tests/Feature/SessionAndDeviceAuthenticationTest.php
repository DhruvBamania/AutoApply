<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionAndDeviceAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    public function test_login_records_login_time_and_clears_other_device_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'singledevicetest@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Simulate an existing session for this user (e.g. Device A)
        $deviceASessionId = 'device_a_session_' . uniqid();
        DB::table('sessions')->insert([
            'id' => $deviceASessionId,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Device A Browser',
            'payload' => base64_encode(serialize(['test' => true])),
            'last_activity' => time(),
        ]);

        $this->assertDatabaseHas('sessions', [
            'id' => $deviceASessionId,
            'user_id' => $user->id,
        ]);

        // Now user logs in from Device B
        $response = $this->post('/login', [
            'email' => 'singledevicetest@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify Device A's session was deleted from database
        $this->assertDatabaseMissing('sessions', [
            'id' => $deviceASessionId,
        ]);

        // Clean up test user
        $user->delete();
    }

    public function test_active_session_within_7_days_remains_logged_in(): void
    {
        $user = User::factory()->create();

        // 3 days ago login
        $threeDaysAgo = now()->subDays(3)->timestamp;

        $response = $this->actingAs($user)
            ->withSession(['login_time' => $threeDaysAgo])
            ->get('/dashboard');

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        $user->delete();
    }

    public function test_session_expires_after_7_days(): void
    {
        $user = User::factory()->create();

        // 8 days ago login (expired)
        $eightDaysAgo = now()->subDays(8)->timestamp;

        $response = $this->actingAs($user)
            ->withSession(['login_time' => $eightDaysAgo])
            ->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Your 7-day session has expired. Please log in again.');
        $this->assertGuest();

        $user->delete();
    }
}
