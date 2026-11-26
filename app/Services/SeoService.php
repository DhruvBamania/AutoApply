<?php

namespace App\Services;

use App\Models\JobPosting;

class SeoService
{
    public const DEFAULT_TITLE = 'AutoApply - Autonomous AI Job Hunt & Recruitment Automation Platform';
    public const DEFAULT_DESCRIPTION = 'AutoApply automates job search and recruitment with AI: 1-click ATS resume tailoring, direct WhatsApp candidate messaging, multi-platform Chrome companion, and instant job matching.';
    public const DEFAULT_KEYWORDS = 'AI job search, autonomous job apply, ATS resume builder, WhatsApp recruiting, Gemini AI candidate matching, Chrome job companion, headhunter agent, automated job applications';

    /**
     * Resolve page title with clean brand suffix
     */
    public static function title(?string $pageTitle = null): string
    {
        if (empty($pageTitle)) {
            return self::DEFAULT_TITLE;
        }

        if (str_contains($pageTitle, 'AutoApply') || str_contains($pageTitle, 'AI Looping')) {
            return $pageTitle;
        }

        return "{$pageTitle} | AutoApply";
    }

    /**
     * Resolve page description (capped at 160 characters for optimal SERP snippets)
     */
    public static function description(?string $desc = null): string
    {
        $raw = !empty($desc) ? $desc : self::DEFAULT_DESCRIPTION;
        $cleaned = trim(preg_replace('/\s+/', ' ', strip_tags($raw)));
        return mb_substr($cleaned, 0, 160);
    }

    /**
     * Resolve page keywords
     */
    public static function keywords(?string $keywords = null): string
    {
        if (!empty($keywords)) {
            return $keywords;
        }
        return self::DEFAULT_KEYWORDS;
    }

    /**
     * Resolve canonical URL
     */
    public static function canonicalUrl(?string $url = null): string
    {
        if (!empty($url)) {
            return $url;
        }
        return url()->current();
    }

    /**
     * Resolve OpenGraph social share image URL
     */
    public static function ogImage(?string $customImage = null): string
    {
        if (!empty($customImage)) {
            return filter_var($customImage, FILTER_VALIDATE_URL) ? $customImage : url($customImage);
        }
        return url('images/og-image.png');
    }

    /**
     * Generate Google for Jobs Schema.org JobPosting JSON-LD
     */
    public static function generateJobPostingJsonLd(JobPosting $job): array
    {
        $companyName = $job->user->companyProfile->company_name ?? $job->user->name ?? 'Verified Employer';
        $location = $job->location ?? 'Remote, Universal';
        $isRemote = stripos($location, 'remote') !== false || stripos($job->title, 'remote') !== false;

        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => nl2br(e($job->description)),
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => 'AutoApply Job ID',
                'value' => (string) $job->id,
            ],
            'datePosted' => $job->created_at ? $job->created_at->toIso8601String() : now()->toIso8601String(),
            'validThrough' => $job->created_at ? $job->created_at->addDays(60)->toIso8601String() : now()->addDays(60)->toIso8601String(),
            'employmentType' => strtoupper(str_replace('-', '_', $job->job_type ?? 'FULL_TIME')),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $companyName,
                'sameAs' => url('/'),
                'logo' => url('images/logo.png'),
            ],
            'jobLocationType' => $isRemote ? 'TELECOMMUTE' : null,
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $location,
                    'addressCountry' => 'Universal',
                ],
            ],
            'directApply' => true,
            'url' => route('jobs.public.show', $job->id),
        ];

        $salaryMin = $job->salary_min ?? null;
        $salaryMax = $job->salary_max ?? null;

        if (empty($salaryMin) && !empty($job->salary_range)) {
            preg_match_all('/\d[\d,.]*/', $job->salary_range, $matches);
            if (!empty($matches[0])) {
                $numbers = array_map(fn($n) => (float) str_replace(',', '', $n), $matches[0]);
                $salaryMin = $numbers[0] ?? null;
                $salaryMax = $numbers[1] ?? $salaryMin;
            }
        }

        if (!empty($salaryMin) || !empty($salaryMax)) {
            $schema['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => 'USD',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => (float) ($salaryMin ?? 0),
                    'maxValue' => (float) ($salaryMax ?? $salaryMin),
                    'unitText' => 'YEAR',
                ],
            ];
        }

        return array_filter($schema);
    }

    /**
     * Generate Schema.org Organization JSON-LD
     */
    public static function generateOrganizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'AutoApply',
            'alternateName' => 'AI Looping',
            'url' => url('/'),
            'logo' => url('images/logo.png'),
            'description' => self::DEFAULT_DESCRIPTION,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+1-800-AUTO-APPLY',
                'contactType' => 'customer support',
                'availableLanguage' => ['English', 'Spanish', 'Hindi', 'French', 'German'],
            ],
            'sameAs' => [
                'https://github.com/DhruvBamania/AutoApply',
                'https://linkedin.com',
                'https://twitter.com',
            ],
        ];
    }

    /**
     * Generate Schema.org WebSite JSON-LD with Sitelinks SearchBox
     */
    public static function generateWebSiteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'AutoApply',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/jobs?search={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Generate Schema.org SoftwareApplication JSON-LD for Chrome Extension & Mobile App
     */
    public static function generateSoftwareApplicationJsonLd(string $type = 'extension'): array
    {
        if ($type === 'extension') {
            return [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => 'AutoApply AI Chrome Companion',
                'operatingSystem' => 'Chrome, Brave, Edge',
                'applicationCategory' => 'BusinessApplication',
                'downloadUrl' => route('chrome_extension.download'),
                'featureList' => [
                    '1-Click ATS Resume Matching on LinkedIn & Indeed',
                    'Autonomous Screening Question Answer Generation',
                    'Universal Application Form AutoFill',
                    'Multi-Platform Job Application Tracker',
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.9',
                    'ratingCount' => '128',
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'MobileApplication',
            'name' => 'AutoApply Mobile App',
            'operatingSystem' => 'Android 8.0+',
            'applicationCategory' => 'BusinessApplication',
            'downloadUrl' => route('apk.download'),
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];
    }

    /**
     * Generate Schema.org BreadcrumbList JSON-LD
     */
    public static function generateBreadcrumbJsonLd(array $items): array
    {
        $elements = [];
        $position = 1;

        foreach ($items as $name => $url) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => filter_var($url, FILTER_VALIDATE_URL) ? $url : url($url),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }
}
