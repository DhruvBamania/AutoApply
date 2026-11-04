<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalLanguageTranslateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_universal_language_translator_in_header_and_mobile_drawer(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('id="langDropdownWrapper"', false);
        $response->assertSee('id="langPickerBtn"', false);
        $response->assertSee('id="langDropdownMenu"', false);
        $response->assertSee('id="langSearchInput"', false);
        $response->assertSee('id="mobileLangSelect"', false);
        $response->assertSee('id="google_translate_element"', false);
        $response->assertSee('googleTranslateElementInit', false);
    }

    public function test_authenticated_candidate_sees_universal_language_translator(): void
    {
        $candidate = User::factory()->create([
            'role' => 'user',
            'email' => 'candidate_translate@example.com',
        ]);

        $response = $this->actingAs($candidate)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="langDropdownWrapper"', false);
        $response->assertSee('id="langPickerBtn"', false);
        $response->assertSee('id="mobileLangSelect"', false);
        $response->assertSee('googleTranslateElementInit', false);
    }

    public function test_authenticated_company_sees_universal_language_translator(): void
    {
        $companyUser = User::factory()->create([
            'role' => 'company',
            'email' => 'company_translate@example.com',
        ]);

        $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="langDropdownWrapper"', false);
        $response->assertSee('id="langPickerBtn"', false);
        $response->assertSee('id="mobileLangSelect"', false);
        $response->assertSee('googleTranslateElementInit', false);
    }
}
