<?php

namespace App\Services;

use App\Models\User;

class DeepLinkService
{
    public const SCHEME = 'autoapply';
    public const ALT_SCHEME = 'ailooping';

    /**
     * Generate custom URI scheme deep link (e.g. autoapply://jobs/12)
     */
    public static function createCustomSchemeUrl(string $path, array $queryParams = []): string
    {
        $cleanPath = ltrim($path, '/');
        $url = self::SCHEME . '://' . $cleanPath;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $url;
    }

    /**
     * Generate universal web link for smart redirect & fallback (e.g. https://.../open-app?target=job&id=12)
     */
    public static function createUniversalUrl(string $target, array $params = []): string
    {
        $params['target'] = $target;
        return url('/open-app?' . http_build_query($params));
    }

    /**
     * Deep link for a specific job posting
     */
    public static function forJob(int|string $jobId): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl("jobs/{$jobId}"),
            'universal' => self::createUniversalUrl('job', ['id' => $jobId]),
        ];
    }

    /**
     * Deep link for exploring all jobs
     */
    public static function forExploreJobs(): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl('jobs'),
            'universal' => self::createUniversalUrl('jobs'),
        ];
    }

    /**
     * Deep link for candidate scouted opportunities
     */
    public static function forScouted(): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl('scouted'),
            'universal' => self::createUniversalUrl('scouted'),
        ];
    }

    /**
     * Deep link for AI Interview Prep
     */
    public static function forInterviewPrep(): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl('interview-prep'),
            'universal' => self::createUniversalUrl('interview_prep'),
        ];
    }

    /**
     * Deep link for Resume Builder
     */
    public static function forResumeBuilder(): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl('resume-builder'),
            'universal' => self::createUniversalUrl('resumes'),
        ];
    }

    /**
     * Deep link for Company inquiries & candidate comments
     */
    public static function forInquiries(): array
    {
        return [
            'deeplink' => self::createCustomSchemeUrl('company/inquiries'),
            'universal' => self::createUniversalUrl('inquiries'),
        ];
    }

    /**
     * Generate 1-click mobile authentication link for a user
     */
    public static function forAuth(User $user, ?string $target = null, array $additionalParams = []): array
    {
        $token = $user->createToken('Mobile DeepLink AutoAuth')->plainTextToken;
        $params = array_merge([
            'token' => $token,
            'role' => $user->role,
        ], $additionalParams);

        if ($target) {
            $params['target'] = $target;
        }

        return [
            'deeplink' => self::createCustomSchemeUrl('auth/login', $params),
            'universal' => self::createUniversalUrl('auth', $params),
            'token' => $token,
        ];
    }
}
