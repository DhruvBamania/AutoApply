<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyJobApiController;
use App\Http\Controllers\Api\CompanyInquiryApiController;
use App\Http\Controllers\Api\CompanyCandidateMatchApiController;
use App\Http\Controllers\Api\CandidateResumeApiController;
use App\Http\Controllers\Api\PublicJobApiController;
use App\Http\Controllers\Api\CandidateCampaignApiController;

/*
|--------------------------------------------------------------------------
| Mobile Application REST API Routes (v1)
|--------------------------------------------------------------------------
|
| Connected with Flutter Mobile Client using Laravel Sanctum Bearer tokens.
|
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. Authentication (Public)
    // ==========================================
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ==========================================
    // 2. Public Job Discovery & Applications
    // ==========================================
    Route::get('/jobs', [PublicJobApiController::class, 'index']);
    Route::get('/jobs/{id}', [PublicJobApiController::class, 'show']);
    Route::post('/jobs/{id}/apply', [PublicJobApiController::class, 'apply']);
    Route::post('/jobs/{id}/whatsapp-message', [PublicJobApiController::class, 'whatsappMessage']);

    // ==========================================
    // 3. Public Inbound Contact & Inquiries
    // ==========================================
    Route::post('/contact', [\App\Http\Controllers\Api\ContactApiController::class, 'submit']);

    // ==========================================
    // 4. Authenticated Routes (Sanctum Protected)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // Session & Profile
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // --- COMPANY ENDPOINTS ---
        Route::prefix('company')->group(function () {
            // Profile & WhatsApp Settings
            Route::put('/profile/whatsapp', [\App\Http\Controllers\CompanyDashboardController::class, 'updateWhatsApp']);

            // 1-Click Multi-Platform Job Postings
            Route::get('/jobs', [CompanyJobApiController::class, 'index']);
            Route::post('/jobs', [CompanyJobApiController::class, 'store']);
            Route::get('/jobs/{id}', [CompanyJobApiController::class, 'show']);
            Route::post('/jobs/{id}/repost', [CompanyJobApiController::class, 'repost']);
            Route::delete('/jobs/{id}', [CompanyJobApiController::class, 'destroy']);

            // Central Inquiries Hub, Query Pinning, & Responses
            Route::get('/inquiries', [CompanyInquiryApiController::class, 'index']);
            Route::post('/inquiries/{id}/pin', [CompanyInquiryApiController::class, 'togglePin']);
            Route::patch('/inquiries/{id}/status', [CompanyInquiryApiController::class, 'updateStatus']);
            Route::post('/inquiries/{id}/reply', [CompanyInquiryApiController::class, 'reply']);
            Route::post('/inquiries/{id}/whatsapp-reply', [CompanyInquiryApiController::class, 'replyWhatsApp']);
            Route::post('/inquiries/sync', [CompanyInquiryApiController::class, 'sync']);
            Route::post('/inquiries/manual', [CompanyInquiryApiController::class, 'storeManual']);

            // Gemini AI Candidate Matching & Leaderboard
            Route::post('/jobs/{id}/ai-match', [CompanyCandidateMatchApiController::class, 'match']);
            Route::get('/jobs/{id}/leaderboard', [CompanyCandidateMatchApiController::class, 'leaderboard']);
            Route::post('/jobs/{id}/ai-shortlist-top', [CompanyCandidateMatchApiController::class, 'batchShortlist']);

            // AI Headhunter Agent Endpoints
            Route::get('/jobs/{id}/headhunter', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'jobMatches']);
            Route::post('/jobs/{id}/headhunter/scout', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'scout']);
            Route::post('/headhunter/{id}/invite', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'invite']);
            Route::post('/headhunter/{id}/whatsapp-outreach', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'whatsAppOutreach']);
            Route::post('/jobs/{id}/headhunter/batch-invite', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'batchInvite']);
        });

        // --- CANDIDATE ENDPOINTS ---
        Route::prefix('candidate')->group(function () {
            // Resume Parsing, Templates, and AI Tailoring
            Route::get('/resume', [CandidateResumeApiController::class, 'show']);
            Route::post('/resume/upload', [CandidateResumeApiController::class, 'upload']);
            Route::post('/resume/tailor', [CandidateResumeApiController::class, 'tailor']);
            Route::get('/templates', [CandidateResumeApiController::class, 'templates']);
            Route::post('/resume/export', [CandidateResumeApiController::class, 'export']);

            // Outbound Campaigns & Lead Generation
            Route::get('/campaigns', [CandidateCampaignApiController::class, 'campaigns']);
            Route::post('/campaigns', [CandidateCampaignApiController::class, 'storeCampaign']);
            Route::post('/campaigns/{id}/generate', [CandidateCampaignApiController::class, 'generateCampaignLeads']);

            // AI Social Posts Generation & Approval
            Route::get('/social-posts', [CandidateCampaignApiController::class, 'socialPosts']);
            Route::post('/social-posts/generate', [CandidateCampaignApiController::class, 'generateSocialPosts']);
            Route::post('/social-posts/{id}/approve', [CandidateCampaignApiController::class, 'approveSocialPost']);

            // AI Dynamic Interview Preparation Hub
            Route::get('/interview-prep', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'index']);
            Route::get('/interview-prep/profile', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'resumeProfile']);
            Route::post('/interview-prep/generate', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'generate']);
            Route::get('/interview-prep/{id}', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'show']);
            Route::post('/interview-prep/{id}/bookmark', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'toggleBookmark']);
            Route::delete('/interview-prep/{id}', [\App\Http\Controllers\Api\InterviewPrepApiController::class, 'destroy']);

            // AI Headhunter Scouted Opportunities
            Route::get('/headhunter/invitations', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'candidateInvitations']);
            Route::post('/headhunter/invitations/{id}/respond', [\App\Http\Controllers\Api\HeadhunterApiController::class, 'respondInvitation']);
        });

    });

});
