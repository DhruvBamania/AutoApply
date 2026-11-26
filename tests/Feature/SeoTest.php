<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use App\Services\SeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_returns_valid_xml_structure(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $content);
        $this->assertStringContainsString(url('/'), $content);
        $this->assertStringContainsString(route('jobs.index'), $content);
        $this->assertStringContainsString(route('chrome_extension.index'), $content);
        $this->assertStringContainsString(route('app.download.page'), $content);
        $this->assertStringContainsString(route('contact'), $content);
        $this->assertStringContainsString(route('terms'), $content);
        $this->assertStringContainsString(route('privacy'), $content);
        $this->assertStringContainsString(route('cookies'), $content);
    }

    public function test_sitemap_includes_active_job_postings(): void
    {
        $employer = User::factory()->create(['role' => 'company']);
        $job = JobPosting::create([
            'user_id' => $employer->id,
            'title' => 'Senior AI Platform Architect',
            'description' => 'Architecting scalable autonomous job search and recruitment infrastructure with Gemini AI.',
            'job_type' => 'full-time',
            'location' => 'San Francisco, CA (Remote)',
            'salary_range' => '$160,000 - $210,000',
            'status' => 'active',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString(route('jobs.public.show', $job->id), $response->getContent());
        $this->assertStringContainsString('<priority>0.8</priority>', $response->getContent());
    }

    public function test_robots_txt_returns_proper_rules_and_sitemap_pointer(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /company/', $content);
        $this->assertStringContainsString('Disallow: /candidate/', $content);
        $this->assertStringContainsString('Disallow: /api/', $content);
        $this->assertStringContainsString('Sitemap: https://auto-apply-two-phi.vercel.app/sitemap.xml', $content);
    }

    public function test_job_details_page_includes_job_posting_json_ld_schema(): void
    {
        $employer = User::factory()->create([
            'name' => 'Stripe Talent Team',
            'role' => 'company',
        ]);

        $job = JobPosting::create([
            'user_id' => $employer->id,
            'title' => 'Staff Machine Learning Engineer',
            'description' => 'Build high-throughput LLM pipelines and embedding search models for autonomous agents.',
            'job_type' => 'full-time',
            'location' => 'Seattle, WA (Hybrid)',
            'salary_range' => '$180,000 - $240,000',
            'status' => 'active',
        ]);

        $response = $this->get(route('jobs.public.show', $job->id));

        $response->assertStatus(200);
        $response->assertSee('Staff Machine Learning Engineer', false);
        $response->assertSee('"@type": "JobPosting"', false);
        $response->assertSee('"title": "Staff Machine Learning Engineer"', false);
        $response->assertSee('Stripe Talent Team', false);
        $response->assertSee('"@type": "MonetaryAmount"', false);
        $response->assertSee('"currency": "USD"', false);
    }

    public function test_chrome_extension_page_includes_software_application_json_ld(): void
    {
        $response = $this->get(route('chrome_extension.index'));

        $response->assertStatus(200);
        $response->assertSee('"@type": "SoftwareApplication"', false);
        $response->assertSee('AutoApply AI Chrome Companion', false);
        $response->assertSee(route('chrome_extension.download'), false);
    }

    public function test_pages_include_open_graph_and_twitter_meta_tags(): void
    {
        $response = $this->get(route('jobs.index'));

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:type"', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:image"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('og-image.png', false);
    }

    public function test_seo_service_generates_valid_schemas(): void
    {
        $org = SeoService::generateOrganizationJsonLd();
        $this->assertEquals('AutoApply', $org['name']);
        $this->assertEquals('Organization', $org['@type']);

        $site = SeoService::generateWebSiteJsonLd();
        $this->assertEquals('WebSite', $site['@type']);
        $this->assertArrayHasKey('potentialAction', $site);

        $crumbs = SeoService::generateBreadcrumbJsonLd([
            'Home' => '/',
            'Jobs' => '/jobs',
            'Role' => '/jobs/1',
        ]);
        $this->assertEquals('BreadcrumbList', $crumbs['@type']);
        $this->assertCount(3, $crumbs['itemListElement']);
    }
}
