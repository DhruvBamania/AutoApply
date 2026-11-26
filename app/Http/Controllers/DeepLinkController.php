<?php

namespace App\Http\Controllers;

use App\Services\DeepLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeepLinkController extends Controller
{
    /**
     * Android Digital Asset Links verification file.
     * Served at /.well-known/assetlinks.json
     */
    public function assetLinks(): JsonResponse
    {
        $package = config('services.mobile_app.package_name', 'com.ailooping.mobile_app');
        $sha256 = config('services.mobile_app.sha256_cert', '14:6D:E9:01:C3:59:E1:9F:5B:95:29:73:25:7E:34:6F:E1:0C:5E:82:AE:65:64:B4:A0:68:1B:47:3B:56:4C:E6');

        return response()->json([
            [
                'relation' => [
                    'delegate_permission/common.handle_all_urls',
                ],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $package,
                    'sha256_cert_fingerprints' => [
                        $sha256,
                    ],
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }

    /**
     * Apple App Site Association file for iOS Universal Links.
     * Served at /.well-known/apple-app-site-association
     */
    public function appleAppSiteAssociation(): JsonResponse
    {
        $appId = config('services.mobile_app.apple_app_id', 'TEAMID.com.ailooping.mobileApp');

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appID' => $appId,
                        'paths' => ['*'],
                    ],
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }

    /**
     * Universal Smart Redirector Page (/open-app and /app/open).
     * Opens the mobile app directly via custom scheme, with instant fallback to APK download or web page.
     */
    public function open(Request $request)
    {
        $target = $request->query('target', 'jobs');
        $id = $request->query('id');
        $token = $request->query('token');
        $role = $request->query('role');

        $query = [];
        if ($token) $query['token'] = $token;
        if ($role) $query['role'] = $role;

        // Resolve deep link path and web fallback URL
        switch ($target) {
            case 'job':
            case 'jobs':
                $deepPath = $id ? "jobs/{$id}" : 'jobs';
                $webFallback = $id ? route('jobs.public.show', $id) : route('jobs.index');
                $targetTitle = $id ? "Job Opening #{$id}" : 'Explore Job Openings';
                break;

            case 'scouted':
            case 'headhunter':
                $deepPath = 'scouted';
                $webFallback = route('candidate.headhunter.index');
                $targetTitle = 'Scouted Opportunities';
                break;

            case 'resumes':
            case 'resume-builder':
                $deepPath = 'resume-builder';
                $webFallback = route('resumes.tailor');
                $targetTitle = 'AI Resume Studio';
                break;

            case 'interview_prep':
            case 'interview-prep':
                $deepPath = 'interview-prep';
                $webFallback = route('interview_prep.index');
                $targetTitle = 'AI Interview Prep';
                break;

            case 'mock_interview':
            case 'mock-interview':
                $deepPath = 'mock-interview';
                $webFallback = route('interview_prep.index');
                $targetTitle = 'Turn-by-Turn Mock Interview';
                break;

            case 'inquiries':
                $deepPath = 'company/inquiries';
                $webFallback = route('company.inquiries.index');
                $targetTitle = 'Candidate Inquiries & Leads';
                break;

            case 'dashboard':
            case 'company_dashboard':
                $deepPath = 'company/dashboard';
                $webFallback = route('company.dashboard');
                $targetTitle = 'Company Dashboard';
                break;

            case 'create_job':
            case 'post_job':
                $deepPath = 'company/create-job';
                $webFallback = route('company.jobs.create');
                $targetTitle = 'Post a New Job';
                break;

            case 'auth':
            case 'login':
                $deepPath = 'auth/login';
                $webFallback = route('login');
                $targetTitle = 'Sign In to AutoApply';
                break;

            default:
                $deepPath = ltrim($target, '/');
                $webFallback = url('/');
                $targetTitle = 'AutoApply AI Looping';
                break;
        }

        $deepLinkUrl = DeepLinkService::createCustomSchemeUrl($deepPath, $query);
        $altDeepLinkUrl = 'ailooping://' . ltrim($deepPath, '/') . (!empty($query) ? '?' . http_build_query($query) : '');
        $downloadUrl = route('app.download.page');
        $apkDirectUrl = route('apk.download');

        return view('pages.open_app', compact(
            'target',
            'deepLinkUrl',
            'altDeepLinkUrl',
            'webFallback',
            'downloadUrl',
            'apkDirectUrl',
            'targetTitle'
        ));
    }
}
