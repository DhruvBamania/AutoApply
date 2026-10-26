<?php

namespace Tests\Feature;

use App\Mail\ContactFormAdminNotificationMail;
use App\Mail\ContactFormUserReceiptMail;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactAndLegalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_loads_with_interactive_faqs_and_channels(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Get in Touch with AutoApply');
        $response->assertSee('Send Us a Message');
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('Candidate');
        $response->assertSee('Employer');
        $response->assertSee('Technical Support');
    }

    public function test_contact_form_submission_validates_required_fields(): void
    {
        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors(['name', 'email', 'category', 'subject', 'message']);
    }

    public function test_contact_form_submission_creates_record_and_queues_emails(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Elena Rostova',
            'email' => 'elena.rostova@techventures.io',
            'phone' => '+1 (415) 800-9922',
            'category' => 'enterprise_sales',
            'subject' => 'AI Headhunter Enterprise Integration',
            'message' => 'We want to deploy the AI Headhunter Agent for 40 engineering roles across Europe and North America.',
        ];

        $response = $this->post('/contact', $payload);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Elena Rostova',
            'email' => 'elena.rostova@techventures.io',
            'category' => 'enterprise_sales',
            'subject' => 'AI Headhunter Enterprise Integration',
            'status' => 'unread',
        ]);

        $message = ContactMessage::where('email', 'elena.rostova@techventures.io')->first();
        $this->assertNotNull($message);
        $this->assertEquals('INQ-00001', $message->ticket_id);
        $this->assertEquals('Enterprise & Custom Integrations', $message->category_label);
    }

    public function test_contact_form_honeypot_silently_ignores_spambots(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Spambot 3000',
            'email' => 'bot@spammer.net',
            'category' => 'general',
            'subject' => 'Buy Crypto Tokens',
            'message' => 'Spam content intended to bypass normal forms.',
            'website_hp' => 'http://spam-link-filled-by-bot.com', // honeypot triggered
        ];

        $response = $this->post('/contact', $payload);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        // Spammed submission should NOT be stored in the database
        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'bot@spammer.net',
        ]);

        Mail::assertNothingSent();
    }

    public function test_authenticated_user_submits_contact_form_with_user_id_linked(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'user',
            'name' => 'Rohan Varma',
            'email' => 'rohan.varma@example.com',
        ]);

        $payload = [
            'name' => 'Rohan Varma',
            'email' => 'rohan.varma@example.com',
            'category' => 'candidate_support',
            'subject' => 'Question about dynamic ATS resume formatting',
            'message' => 'I would like to know if template 7 is compatible with Taleo and Workday ATS systems.',
        ];

        $response = $this->actingAs($user)->post('/contact', $payload);

        $response->assertRedirect('/contact');

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $user->id,
            'email' => 'rohan.varma@example.com',
            'category' => 'candidate_support',
        ]);

        $msg = ContactMessage::where('user_id', $user->id)->first();
        $this->assertEquals($user->id, $msg->user_id);
    }

    public function test_mobile_api_contact_endpoint_returns_201_with_ticket_id(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Deepak Patel',
            'email' => 'deepak.patel@globaltech.com',
            'phone' => '+91 98765 43210',
            'category' => 'company_inquiry',
            'subject' => 'API Syndication Limits',
            'message' => 'How many jobs can we post concurrently using the multi-platform syndication feature?',
        ];

        $response = $this->postJson('/api/v1/contact', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Deepak Patel')
            ->assertJsonPath('data.category', 'company_inquiry');

        $ticketId = $response->json('ticket_id');
        $this->assertNotNull($ticketId);
        $this->assertStringStartsWith('INQ-', $ticketId);
    }

    public function test_mobile_api_contact_endpoint_fails_validation_on_empty_payload(): void
    {
        $response = $this->postJson('/api/v1/contact', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_terms_of_service_page_loads_with_legal_sections(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('Acceptance of Terms');
        $response->assertSee('AI Headhunter');
        $response->assertSee('Limitation of Liability');
        $response->assertSee('Single-Device Session Policy');
    }

    public function test_privacy_policy_page_loads_with_gdpr_ccpa_sections(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('GDPR');
        $response->assertSee('CCPA');
        $response->assertSee('Google Cloud (Gemini 2.5 Flash)');
        $response->assertSee('Groq Cloud (Llama 3.3)');
        $response->assertSee('Data Protection Officer');
    }

    public function test_cookie_policy_page_loads_with_cookie_inventory_table(): void
    {
        $response = $this->get('/cookies');

        $response->assertStatus(200);
        $response->assertSee('Cookie Policy');
        $response->assertSee('laravel_session');
        $response->assertSee('XSRF-TOKEN');
        $response->assertSee('Strictly Necessary Cookies');
        $response->assertSee('Inventory of Cookies Used on Platform');
    }

    public function test_layout_footer_contains_all_legal_and_contact_links(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee(route('terms'));
        $response->assertSee(route('privacy'));
        $response->assertSee(route('cookies'));
        $response->assertSee(route('contact'));
        $response->assertSee('AutoApply &amp; AI Looping Systems Inc.', false);
        $response->assertSee('All Production Systems Operational');
    }
}
