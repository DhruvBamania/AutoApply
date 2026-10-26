<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RemoteMigrationRouteTest extends TestCase
{
    public function test_migrate_route_rejects_unauthorized_access(): void
    {
        $response = $this->get('/migrate');
        $response->assertStatus(403);
        $response->assertJsonFragment(['status' => 'error']);

        $responseInvalid = $this->get('/migrate?secret=wrong_secret');
        $responseInvalid->assertStatus(403);
    }

    public function test_migrate_route_status_check_with_valid_secret(): void
    {
        $response = $this->get('/migrate?secret=ailoop_migrate_2026&action=status');
        $response->assertStatus(200);
        $this->assertStringContainsString('Database Operations Output', $response->getContent());
        $this->assertStringContainsString('Migration status check completed', $response->getContent());
    }

    public function test_migrate_route_json_response(): void
    {
        $response = $this->getJson('/migrate?secret=ailoop_migrate_2026&action=status');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'database' => ['host', 'database', 'prefix'],
            'output',
        ]);
    }
}
