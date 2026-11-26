<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap compliant with sitemaps.org protocol.
     */
    public function index(): Response
    {
        $urls = [];

        // 1. Core High-Priority Public Landing Pages
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => route('jobs.index'),
            'lastmod' => now()->startOfHour()->toAtomString(),
            'changefreq' => 'hourly',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('chrome_extension.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('app.download.page'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];

        $urls[] = [
            'loc' => route('contact'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ];

        $urls[] = [
            'loc' => route('terms'),
            'lastmod' => now()->startOfYear()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        $urls[] = [
            'loc' => route('privacy'),
            'lastmod' => now()->startOfYear()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        $urls[] = [
            'loc' => route('cookies'),
            'lastmod' => now()->startOfYear()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        // 2. All Active Job Postings
        $activeJobs = JobPosting::where('status', 'active')
            ->latest('updated_at')
            ->take(500)
            ->get();

        foreach ($activeJobs as $job) {
            $urls[] = [
                'loc' => route('jobs.public.show', $job->id),
                'lastmod' => $job->updated_at ? $job->updated_at->toAtomString() : now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        // 3. Render XML output
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $u) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "        <lastmod>{$u['lastmod']}</lastmod>\n";
            $xml .= "        <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "        <priority>{$u['priority']}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
