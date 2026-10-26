<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DraftController;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isCompany()
            ? redirect()->route('company.dashboard')
            : redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Company Protected Routes
Route::middleware(['auth', 'role:company'])->group(function () {
    Route::get('/company/dashboard', [\App\Http\Controllers\CompanyDashboardController::class, 'index'])->name('company.dashboard');

    // 1-Click Job Postings (LinkedIn, Naukri, Indeed)
    Route::get('/company/jobs', [\App\Http\Controllers\CompanyJobController::class, 'index'])->name('company.jobs.index');
    Route::get('/company/jobs/create', [\App\Http\Controllers\CompanyJobController::class, 'create'])->name('company.jobs.create');
    Route::post('/company/jobs', [\App\Http\Controllers\CompanyJobController::class, 'store'])->name('company.jobs.store');
    Route::get('/company/jobs/{id}', [\App\Http\Controllers\CompanyJobController::class, 'show'])->name('company.jobs.show');
    Route::post('/company/jobs/{id}/repost', [\App\Http\Controllers\CompanyJobController::class, 'repost'])->name('company.jobs.repost');
    Route::delete('/company/jobs/{id}', [\App\Http\Controllers\CompanyJobController::class, 'destroy'])->name('company.jobs.destroy');
    Route::post('/company/jobs/{id}/ai-match', [\App\Http\Controllers\CompanyJobController::class, 'matchCandidates'])->name('company.jobs.ai_match');
    Route::post('/company/jobs/{id}/ai-shortlist-top', [\App\Http\Controllers\CompanyJobController::class, 'batchShortlist'])->name('company.jobs.ai_shortlist');

    // Company Integrations & Feeds
    Route::get('/company/integrations', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('company.integrations');
    Route::get('/company/feed/indeed.xml', [\App\Http\Controllers\CompanyFeedController::class, 'indeedFeed'])->name('company.feed.indeed');

    // Centralized Candidate Inquiries & Comments Hub (Job Role Wise)
    Route::get('/company/inquiries', [\App\Http\Controllers\CompanyInquiryController::class, 'index'])->name('company.inquiries.index');
    Route::post('/company/inquiries/sync', [\App\Http\Controllers\CompanyInquiryController::class, 'sync'])->name('company.inquiries.sync');
    Route::post('/company/inquiries/manual', [\App\Http\Controllers\CompanyInquiryController::class, 'storeManual'])->name('company.inquiries.manual');
    Route::post('/company/inquiries/seed-demo', [\App\Http\Controllers\CompanyInquiryController::class, 'seedDemo'])->name('company.inquiries.seed');
    Route::patch('/company/inquiries/{id}/status', [\App\Http\Controllers\CompanyInquiryController::class, 'updateStatus'])->name('company.inquiries.status');
    Route::post('/company/inquiries/{id}/reply', [\App\Http\Controllers\CompanyInquiryController::class, 'reply'])->name('company.inquiries.reply');
    Route::post('/company/inquiries/{id}/pin', [\App\Http\Controllers\CompanyInquiryController::class, 'togglePin'])->name('company.inquiries.pin');

    // AI Headhunter Agent (Autonomous Talent Scout & Outreach)
    Route::post('/company/jobs/{id}/headhunter/scout', [\App\Http\Controllers\CompanyHeadhunterController::class, 'scout'])->name('company.headhunter.scout');
    Route::post('/company/headhunter/{id}/invite', [\App\Http\Controllers\CompanyHeadhunterController::class, 'invite'])->name('company.headhunter.invite');
    Route::post('/company/jobs/{id}/headhunter/batch-invite', [\App\Http\Controllers\CompanyHeadhunterController::class, 'batchInvite'])->name('company.headhunter.batch_invite');
});

// Public Candidate Job Opening & Inquiry/Apply Routes
Route::get('/jobs/{id}', [\App\Http\Controllers\PublicJobController::class, 'show'])->name('jobs.public.show');
Route::post('/jobs/{id}/inquire', [\App\Http\Controllers\PublicJobController::class, 'inquire'])->name('jobs.public.inquire');

// Shared Authenticated Routes (Available to both Candidates and Companies)
Route::middleware('auth')->group(function () {
    // Integrations
    Route::get('/integrations', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('integrations.index');
    Route::post('/integrations/{platform}', [\App\Http\Controllers\IntegrationController::class, 'storeCredentials'])->name('integrations.store');
    Route::delete('/integrations/{platform}', [\App\Http\Controllers\IntegrationController::class, 'disconnect'])->name('integrations.disconnect');

    // LinkedIn OAuth
    Route::get('/auth/linkedin/redirect', [\App\Http\Controllers\LinkedInAuthController::class, 'redirect'])->name('linkedin.redirect');
    Route::get('/auth/linkedin/callback', [\App\Http\Controllers\LinkedInAuthController::class, 'callback'])->name('linkedin.callback');

    // AI Dynamic Interview Preparation Hub
    Route::get('/interview-prep', [\App\Http\Controllers\InterviewPrepController::class, 'index'])->name('interview_prep.index');
    Route::post('/interview-prep/generate', [\App\Http\Controllers\InterviewPrepController::class, 'generate'])->name('interview_prep.generate');
    Route::post('/interview-prep/{id}/bookmark', [\App\Http\Controllers\InterviewPrepController::class, 'toggleBookmark'])->name('interview_prep.bookmark');
    Route::delete('/interview-prep/{id}', [\App\Http\Controllers\InterviewPrepController::class, 'destroy'])->name('interview_prep.destroy');
    Route::get('/interview-prep/profile', [\App\Http\Controllers\InterviewPrepController::class, 'resumeProfile'])->name('interview_prep.profile');
});

// Public ATS Feeds
Route::get('/feed/{companyId}/indeed.xml', [\App\Http\Controllers\CompanyFeedController::class, 'publicIndeedFeed'])->name('feed.indeed');

// Candidate / Normal User Protected Routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/resume/upload', [DashboardController::class, 'uploadResume'])->name('resume.upload');

    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{id}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::post('/campaigns/{id}/generate', [CampaignController::class, 'generateLeads'])->name('campaigns.generate');
    Route::post('/campaigns/{id}/stop', [CampaignController::class, 'stop'])->name('campaigns.stop');

    Route::put('/drafts/{id}', [DraftController::class, 'update'])->name('drafts.update');
    Route::post('/drafts/{id}/send', [DraftController::class, 'send'])->name('drafts.send');

    // Social Posts
    Route::get('/social-posts', [\App\Http\Controllers\SocialPostController::class, 'index'])->name('social_posts.index');
    Route::post('/social-posts/generate', [\App\Http\Controllers\SocialPostController::class, 'generate'])->name('social_posts.generate');
    Route::put('/social-posts/{id}', [\App\Http\Controllers\SocialPostController::class, 'update'])->name('social_posts.update');
    Route::post('/social-posts/{id}/approve', [\App\Http\Controllers\SocialPostController::class, 'approve'])->name('social_posts.approve');

    // AI Headhunter Scouted Opportunities for Candidates
    Route::get('/candidate/headhunter-invitations', [\App\Http\Controllers\CandidateHeadhunterController::class, 'index'])->name('candidate.headhunter.index');
    Route::post('/candidate/headhunter-invitations/{id}/respond', [\App\Http\Controllers\CandidateHeadhunterController::class, 'respond'])->name('candidate.headhunter.respond');
});

// A dedicated Postman testing route that bypasses CSRF and Auth
Route::post('/api/test-generate', function (\App\Services\SocialPostGeneratorService $generator) {
    // Automatically use the first user in the database
    $user = \App\Models\User::first();
    
    if (!$user || !$user->resume_text) {
        return response()->json(['error' => 'No user found or no resume text available.'], 400);
    }
    
    try {
        $generator->generateForAllPlatforms($user);
        return response()->json([
            'success' => true, 
            'message' => 'Social posts successfully generated for user: ' . $user->email,
            'posts' => $user->socialPosts
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// A dedicated Postman testing route for the Resume Builder
Route::post('/api/test-resume-tailor', function (\Illuminate\Http\Request $request, \App\Services\ResumeTailoringService $service) {
    // Automatically use the first user in the database
    $user = \App\Models\User::first();
    
    if (!$user || !$user->resume_text) {
        return response()->json(['error' => 'No user found or no base resume text available.'], 400);
    }
    
    $request->validate([
        'company_name' => 'nullable|string|max:255',
        'job_description' => 'required|string',
        'template_id' => 'required|integer|min:1|max:15',
    ]);
    
    try {
        // 1. Generate tailored resume JSON via AI using the exact JSON prompt structure
        $groqService = app(\App\Services\GroqAiService::class);
        $jobDescription = $request->job_description;
        $companyName = $request->company_name ?? 'the target company';
        
        $prompt = "You are an expert ATS resume writer. I want you to tailor the following resume specifically for the job description provided. 
Inject highly relevant, ATS-friendly keywords specific to this role throughout the summary, experience bullets, and skills section.
Target Company: {$companyName}
Target Job Description: {$jobDescription}
Retain the truthful base facts (dates, companies, schools) but rewrite the descriptions to heavily emphasize skills and achievements relevant to this job.
Return ONLY valid JSON that matches this EXACT structure, with no markdown formatting or extra text:
{
  \"name\": \"\",
  \"job_title\": \"\",
  \"contact\": \"\",
  \"summary\": \"\",
  \"skills\": [\"skill1\", \"skill2\"],
  \"experience\": [{\"title\": \"\", \"company\": \"\", \"location\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"projects\": [{\"title\": \"\", \"link\": \"\", \"tech_stack\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"location\": \"\", \"gpa\": \"\", \"date\": \"\"}],
  \"certifications\": [\"cert1\", \"cert2\"]
}

Base Resume Text:
" . $user->resume_text;

        $responseStr = $groqService->generate($prompt);
        if (!$responseStr) {
            throw new \Exception("AI returned empty response.");
        }
        
        // Clean markdown if present
        $jsonStr = str_replace(['```json', '```'], '', $responseStr);
        $jsonStr = trim($jsonStr);
        $resumeData = json_decode($jsonStr, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("AI returned invalid JSON: " . json_last_error_msg());
        }
        
        // Save to TailoredResume model for tracking (optional, but good practice)
        \App\Models\TailoredResume::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'job_description' => $request->job_description,
            'tailored_content' => $jsonStr,
        ]);
        
        // 2. Generate DOCX from the tailored data using the requested template
        $templateId = $request->template_id;
        $classMap = [
            1 => 'Template1Minimal', 2 => 'Template2Modern', 3 => 'Template3Executive',
            4 => 'Template4Creative', 5 => 'Template5Tech', 6 => 'Template6Classic',
            7 => 'Template7Elegant', 8 => 'Template8Clean', 9 => 'Template9Bold',
            10 => 'Template10Startup', 11 => 'Template11Corporate', 12 => 'Template12Academic',
            13 => 'Template13Modern', 14 => 'Template14Simple', 15 => 'Template15Dynamic'
        ];
        
        $fullClassName = "App\\Services\\ResumeTemplates\\" . $classMap[$templateId];
        if (!class_exists($fullClassName)) {
            return response()->json(['error' => 'Template class not found.'], 404);
        }
        
        $templateService = new $fullClassName();
        $tempFile = $templateService->generate($resumeData, 'Word2007');
        
        // 3. Save to public downloads folder
        $downloadsDir = public_path('downloads');
        if (!file_exists($downloadsDir)) {
            mkdir($downloadsDir, 0777, true);
        }
        
        $templateShortName = 'template' . $templateId;
        $nameParts = explode(' ', trim($resumeData['name'] ?? 'User'));
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[count($nameParts)-1] ?? '';
        if (count($nameParts) === 1) $lastName = '';
        
        $finalFilename = $firstName . ($lastName ? '_' . $lastName : '') . '_tailored_' . $templateShortName . '.docx';
        
        $publicPath = $downloadsDir . DIRECTORY_SEPARATOR . $finalFilename;
        copy($tempFile, $publicPath);
        @unlink($tempFile);
        
        // 4. Return JSON response
        return response()->json([
            'success' => true, 
            'message' => 'Resume successfully tailored and generated as DOCX!',
            'template_used' => $templateId,
            'download_url' => url('downloads/' . $finalFilename),
            'tailored_resume' => $resumeData
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// A dedicated API endpoint to create a full resume using custom raw data (name, designation, experience, etc.)
Route::post('/api/create-custom-resume', function (Illuminate\Http\Request $request) {
    try {
        $resumeData = $request->all();
        
        $templateId = $request->input('template_id', 1);
        $classMap = [
            1 => 'Template1Minimal', 2 => 'Template2Modern', 3 => 'Template3Executive',
            4 => 'Template4Creative', 5 => 'Template5Tech', 6 => 'Template6Classic',
            7 => 'Template7Elegant', 8 => 'Template8Clean', 9 => 'Template9Bold',
            10 => 'Template10Startup', 11 => 'Template11Corporate', 12 => 'Template12Academic',
            13 => 'Template13Modern', 14 => 'Template14Simple', 15 => 'Template15Dynamic'
        ];
        
        if (!isset($classMap[$templateId])) {
            return response()->json(['error' => 'Invalid template ID.'], 400);
        }
        
        $fullClassName = "App\\Services\\ResumeTemplates\\" . $classMap[$templateId];
        if (!class_exists($fullClassName)) {
            return response()->json(['error' => 'Template class not found.'], 404);
        }
        
        $templateService = new $fullClassName();
        $tempFile = $templateService->generate($resumeData, 'Word2007');
        
        $downloadsDir = public_path('downloads');
        if (!file_exists($downloadsDir)) {
            mkdir($downloadsDir, 0777, true);
        }
        
        $templateShortName = 'template' . $templateId;
        $nameParts = explode(' ', trim($resumeData['name'] ?? 'User'));
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[count($nameParts)-1] ?? '';
        if (count($nameParts) === 1) $lastName = '';
        
        $finalFilename = $firstName . ($lastName ? '_' . $lastName : '') . '_custom_' . $templateShortName . '.docx';
        
        $publicPath = $downloadsDir . DIRECTORY_SEPARATOR . $finalFilename;
        copy($tempFile, $publicPath);
        @unlink($tempFile);
        
        return response()->json([
            'success' => true, 
            'message' => 'Custom resume successfully generated as DOCX!',
            'template_used' => $templateId,
            'download_url' => url('downloads/' . $finalFilename),
            'resume_data_used' => $resumeData
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// A dedicated Postman testing route for downloading a Resume Template by ID
Route::post('/api/test-template-download', function (\Illuminate\Http\Request $request) {
    $user = \App\Models\User::first();
    
    if (!$user || !$user->resume_json) {
        return response()->json(['error' => 'No user found or no parsed resume JSON available.'], 400);
    }
    
    $request->validate([
        'template_id' => 'required|integer|min:1|max:15',
    ]);
    
    $templateId = $request->template_id;
    $data = json_decode($user->resume_json, true);
    
    // We instantiate the specific template dynamically based on ID
    $className = "App\\Services\\ResumeTemplates\\Template" . $templateId . "Minimal";
    
    // Actually, there's 15 different classes (e.g. Template2Modern, Template3Executive).
    // Let's instantiate it through the app container or manually resolving the class names.
    $classMap = [
        1 => 'Template1Minimal', 2 => 'Template2Modern', 3 => 'Template3Executive',
        4 => 'Template4Creative', 5 => 'Template5Tech', 6 => 'Template6Classic',
        7 => 'Template7Elegant', 8 => 'Template8Clean', 9 => 'Template9Bold',
        10 => 'Template10Startup', 11 => 'Template11Corporate', 12 => 'Template12Academic',
        13 => 'Template13Modern', 14 => 'Template14Simple', 15 => 'Template15Dynamic'
    ];
    
    $fullClassName = "App\\Services\\ResumeTemplates\\" . $classMap[$templateId];
    if (!class_exists($fullClassName)) {
        return response()->json(['error' => 'Template class not found.'], 404);
    }
    
    $templateService = new $fullClassName();
    $tempFile = $templateService->generate($data, 'Word2007');
    
    $templateShortName = 'template' . $templateId;
    $nameParts = explode(' ', trim($data['name'] ?? 'User'));
    $firstName = $nameParts[0] ?? 'User';
    $lastName = $nameParts[count($nameParts)-1] ?? '';
    if (count($nameParts) === 1) $lastName = '';
    
    $finalFilename = $firstName . ($lastName ? '_' . $lastName : '') . '_' . $templateShortName . '_resume.docx';
    
    // Move to public directory so it can be downloaded via URL
    $downloadsDir = public_path('downloads');
    if (!file_exists($downloadsDir)) {
        mkdir($downloadsDir, 0777, true);
    }
    
    $publicPath = $downloadsDir . DIRECTORY_SEPARATOR . $finalFilename;
    copy($tempFile, $publicPath);
    @unlink($tempFile);
    
    return response()->json([
        'success' => true,
        'message' => "Template $templateId successfully generated.",
        'resume_details' => [
            'name' => $data['name'] ?? 'N/A',
            'job_title' => $data['job_title'] ?? 'N/A',
            'email' => $user->email,
            'total_experiences' => isset($data['experience']) ? count($data['experience']) : 0,
            'total_education' => isset($data['education']) ? count($data['education']) : 0,
            'total_skills' => isset($data['skills']) ? count($data['skills']) : 0,
        ],
        'download_url' => url('downloads/' . $finalFilename)
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// =========================================================================
// Dedicated Postman Testing Routes for Job Inquiries & Comments Fetching
// =========================================================================

// 1. Trigger Platform Inquiries & Comments Sync via Postman
Route::post('/api/test-inquiries-sync', function (\Illuminate\Http\Request $request, \App\Services\JobInquiryService $service) {
    $company = $request->company_id 
        ? \App\Models\User::where('role', 'company')->find($request->company_id) 
        : \App\Models\User::where('role', 'company')->first();

    if (!$company) {
        return response()->json(['error' => 'No registered company found in database.'], 404);
    }

    $jobId = $request->input('job_id');
    
    if ($jobId) {
        $job = $company->jobPostings()->find($jobId);
        if (!$job) {
            return response()->json(['error' => "Job ID #{$jobId} not found for company {$company->name}."], 404);
        }
        $syncResult = $service->syncJobInquiries($job);
        $message = "Synced inquiries for '{$job->title}'";
    } else {
        $syncResult = $service->syncAllForCompany($company);
        $message = "Synced inquiries across all jobs for company '{$company->name}'";
    }

    $inquiries = \App\Models\JobInquiry::whereHas('jobPosting', function ($q) use ($company) {
        $q->where('user_id', $company->id);
    })->with('jobPosting:id,title,job_type,location')->latest()->get();

    return response()->json([
        'success' => true,
        'message' => $message,
        'sync_result' => $syncResult,
        'company' => [
            'id' => $company->id,
            'name' => $company->companyProfile->company_name ?? $company->name,
            'email' => $company->email,
        ],
        'total_inquiries_now' => $inquiries->count(),
        'inquiries' => $inquiries,
    ]);
});

// 2. Fetch and View Inquiries Grouped Role-Wise via Postman (GET)
Route::get('/api/test-inquiries', function (\Illuminate\Http\Request $request) {
    $company = $request->query('company_id') 
        ? \App\Models\User::where('role', 'company')->find($request->query('company_id')) 
        : \App\Models\User::where('role', 'company')->first();

    if (!$company) {
        return response()->json(['error' => 'No registered company found in database.'], 404);
    }

    $selectedJobId = $request->query('job_id');
    $selectedPlatform = $request->query('platform');
    $selectedStatus = $request->query('status');
    $selectedType = $request->query('type');

    $allJobs = $company->jobPostings()->with(['inquiries' => function ($q) use ($selectedPlatform, $selectedStatus, $selectedType) {
        if ($selectedPlatform) $q->where('platform', $selectedPlatform);
        if ($selectedStatus) $q->where('status', $selectedStatus);
        if ($selectedType) $q->where('type', $selectedType);
        $q->latest();
    }])->get();

    if ($selectedJobId) {
        $allJobs = $allJobs->where('id', $selectedJobId);
    }

    $totalInquiries = \App\Models\JobInquiry::whereHas('jobPosting', function ($q) use ($company) {
        $q->where('user_id', $company->id);
    })->count();

    $newCount = \App\Models\JobInquiry::whereHas('jobPosting', function ($q) use ($company) {
        $q->where('user_id', $company->id);
    })->where('status', 'new')->count();

    $repliedCount = \App\Models\JobInquiry::whereHas('jobPosting', function ($q) use ($company) {
        $q->where('user_id', $company->id);
    })->where('status', 'replied')->count();

    return response()->json([
        'success' => true,
        'company' => [
            'id' => $company->id,
            'name' => $company->companyProfile->company_name ?? $company->name,
            'email' => $company->email,
        ],
        'metrics' => [
            'total_inquiries' => $totalInquiries,
            'new' => $newCount,
            'replied' => $repliedCount,
            'active_jobs' => $allJobs->count(),
        ],
        'role_wise_inquiries' => $allJobs->map(function ($job) {
            return [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'job_type' => $job->job_type,
                'location' => $job->location,
                'linkedin_post_id' => $job->linkedin_post_id,
                'inquiries_count' => $job->inquiries->count(),
                'inquiries' => $job->inquiries,
            ];
        })->values(),
    ]);
});

// 3. Submit a Candidate Inquiry / Application via Postman
Route::post('/api/test-inquiries-submit', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'job_id' => 'required|exists:job_postings,id',
        'candidate_name' => 'required|string|max:255',
        'candidate_email' => 'nullable|email|max:255',
        'candidate_headline' => 'nullable|string|max:255',
        'message' => 'required|string',
        'platform' => 'nullable|in:linkedin,naukri,indeed,direct',
        'type' => 'nullable|in:query,comment,application',
    ]);

    $job = \App\Models\JobPosting::findOrFail($request->job_id);

    $inquiry = \App\Models\JobInquiry::create([
        'job_posting_id' => $job->id,
        'platform' => $request->platform ?? 'direct',
        'type' => $request->type ?? 'query',
        'candidate_name' => $request->candidate_name,
        'candidate_email' => $request->candidate_email,
        'candidate_headline' => $request->candidate_headline,
        'message' => $request->message,
        'external_id' => 'postman_' . uniqid(),
        'status' => 'new',
    ]);

    return response()->json([
        'success' => true,
        'message' => "Inquiry successfully submitted for job role '{$job->title}'",
        'inquiry' => $inquiry,
    ], 201);
});

// 4. Seed Realistic Multi-Platform Inquiries for Instant Testing via Postman
Route::post('/api/test-inquiries-seed', function (\Illuminate\Http\Request $request) {
    $company = $request->company_id 
        ? \App\Models\User::where('role', 'company')->find($request->company_id) 
        : \App\Models\User::where('role', 'company')->first();

    if (!$company) {
        return response()->json(['error' => 'No registered company found in database.'], 404);
    }

    $job = $request->job_id 
        ? $company->jobPostings()->find($request->job_id) 
        : $company->jobPostings()->first();

    if (!$job) {
        return response()->json(['error' => 'Please post at least one job role first.'], 400);
    }

    $samples = [
        [
            'job_posting_id' => $job->id,
            'platform' => 'linkedin',
            'type' => 'comment',
            'candidate_name' => 'Priya Sharma (Postman Test)',
            'candidate_email' => 'priya.sharma@example.com',
            'candidate_headline' => 'Senior Full-Stack Engineer @ CloudCorp',
            'message' => 'Exciting role! Does your team consider fully remote candidates or is hybrid mandatory in Bengaluru?',
            'external_id' => 'postman_li_' . uniqid(),
            'status' => 'new',
            'created_at' => now()->subHours(2),
        ],
        [
            'job_posting_id' => $job->id,
            'platform' => 'naukri',
            'type' => 'application',
            'candidate_name' => 'Rahul Verma (Postman Test)',
            'candidate_email' => 'rahul.verma@example.com',
            'candidate_headline' => 'Backend Laravel Developer | 4.5 Yrs Exp',
            'message' => 'Applied through Naukri.com. I have experience building scalable REST APIs and queue pipelines. Looking forward to discussing this opportunity.',
            'external_id' => 'postman_naukri_' . uniqid(),
            'status' => 'new',
            'created_at' => now()->subHours(5),
        ],
        [
            'job_posting_id' => $job->id,
            'platform' => 'indeed',
            'type' => 'query',
            'candidate_name' => 'Ananya Patel (Postman Test)',
            'candidate_email' => 'ananya.patel@example.com',
            'candidate_headline' => 'Software Engineer | Microservices Specialist',
            'message' => 'Hello team, what is the expected notice period and interview process timeline for this role?',
            'external_id' => 'postman_indeed_' . uniqid(),
            'status' => 'new',
            'created_at' => now()->subMinutes(40),
        ],
    ];

    $created = [];
    foreach ($samples as $sample) {
        $created[] = \App\Models\JobInquiry::create($sample);
    }

    return response()->json([
        'success' => true,
        'message' => "Seeded 3 realistic sample inquiries (LinkedIn, Naukri, Indeed) for job '{$job->title}'",
        'job_id' => $job->id,
        'job_title' => $job->title,
        'seeded_inquiries' => $created,
    ]);
});

// 5. Test Sending Employer Reply via Postman
Route::post('/api/test-inquiries-reply', function (\Illuminate\Http\Request $request, \App\Services\JobInquiryService $service) {
    $request->validate([
        'inquiry_id' => 'required|exists:job_inquiries,id',
        'company_reply' => 'required|string',
    ]);

    $inquiry = \App\Models\JobInquiry::findOrFail($request->inquiry_id);
    $service->replyToInquiry($inquiry, $request->company_reply);

    return response()->json([
        'success' => true,
        'message' => "Reply recorded and sent to {$inquiry->candidate_name}",
        'inquiry' => $inquiry->fresh(),
    ]);
});

// 6. Test Toggling Pin for Inquiry via Postman
Route::post('/api/test-inquiries-pin', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'inquiry_id' => 'required|exists:job_inquiries,id',
    ]);

    $inquiry = \App\Models\JobInquiry::findOrFail($request->inquiry_id);
    $inquiry->is_pinned = !$inquiry->is_pinned;
    $inquiry->save();

    return response()->json([
        'success' => true,
        'message' => $inquiry->is_pinned 
            ? "📌 Inquiry #{$inquiry->id} pinned to top successfully!" 
            : "Inquiry #{$inquiry->id} unpinned.",
        'inquiry' => $inquiry->fresh(),
    ]);
});

// 7. Test AI Candidate Matching & Ranking via Postman (Gemini AI)
Route::post('/api/test-candidate-matching', function (\Illuminate\Http\Request $request, \App\Services\CandidateMatcherService $matcher) {
    $jobId = $request->input('job_id');
    $job = $jobId 
        ? \App\Models\JobPosting::find($jobId) 
        : \App\Models\JobPosting::has('inquiries')->first();

    if (!$job) {
        return response()->json(['error' => 'No suitable job posting with inquiries found.'], 404);
    }

    $ranked = $matcher->matchAndRankCandidates($job);

    return response()->json([
        'success' => true,
        'message' => "Gemini AI candidate matching complete for '{$job->title}'",
        'job_id' => $job->id,
        'job_title' => $job->title,
        'total_evaluated' => $ranked->count(),
        'top_matches_count' => $job->inquiries()->where('ai_match_score', '>=', 80)->count(),
        'ranked_candidates' => $ranked->map(function ($inq) {
            return [
                'rank' => $inq->ai_rank,
                'candidate_id' => $inq->id,
                'name' => $inq->candidate_name,
                'email' => $inq->candidate_email,
                'headline' => $inq->candidate_headline,
                'match_score' => $inq->ai_match_score,
                'experience_score' => $inq->ai_experience_score,
                'skills_score' => $inq->ai_skills_score,
                'projects_score' => $inq->ai_projects_score,
                'recommendation' => $inq->ai_recommendation,
                'summary' => $inq->ai_summary,
                'matched_skills' => $inq->ai_matched_skills,
                'missing_skills' => $inq->ai_missing_skills,
                'status' => $inq->status,
                'has_resume' => !empty($inq->resume_path),
            ];
        })->values(),
    ]);
});

Route::middleware(['web', 'auth', 'role:user'])->group(function () {
    // Resume Builder / Tailoring
    Route::get('/resume-builder', [\App\Http\Controllers\TailoredResumeController::class, 'index'])->name('resumes.tailor');
    Route::post('/resume-builder/generate', [\App\Http\Controllers\TailoredResumeController::class, 'generate'])->name('resumes.generate');
    Route::get('/resume-builder/{id}', [\App\Http\Controllers\TailoredResumeController::class, 'show'])->name('resumes.show');
    Route::get('/resume-builder/{id}/download', [\App\Http\Controllers\TailoredResumeController::class, 'download'])->name('resumes.download');
    Route::delete('/resume-builder/{id}', [\App\Http\Controllers\TailoredResumeController::class, 'destroy'])->name('resumes.destroy');
    
    // Template Gallery
    Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{id}/preview', [\App\Http\Controllers\TemplateController::class, 'preview'])->name('templates.preview');
    Route::get('/templates/{id}/download', [\App\Http\Controllers\TemplateController::class, 'download'])->name('templates.download');

    // Dynamic Resume Editor
    Route::get('/resume-editor', [\App\Http\Controllers\ResumeEditorController::class, 'index'])->name('resume_editor.index');
    Route::post('/api/resume-editor/preview', [\App\Http\Controllers\ResumeEditorController::class, 'preview'])->name('resume_editor.preview');
    Route::post('/resume-editor/download', [\App\Http\Controllers\ResumeEditorController::class, 'download'])->name('resume_editor.download');

    // Role-based Resume
    Route::get('/role-resume', [\App\Http\Controllers\RoleResumeController::class, 'index'])->name('role_resume.index');
    Route::post('/role-resume/generate', [\App\Http\Controllers\RoleResumeController::class, 'generate'])->name('role_resume.generate');
});

// --- Android Mobile Application Remote Download Routes ---
Route::get('/download-apk', function () {
    // If an external APK URL is configured (e.g., GitHub Releases, Cloudflare R2, Google Drive), redirect to it
    if ($externalUrl = env('APK_DOWNLOAD_URL')) {
        return redirect()->away($externalUrl);
    }

    $apkPath = public_path('app-debug.apk');
    if (!file_exists($apkPath)) {
        $sourcePath = base_path('mobile_app/build/app/outputs/flutter-apk/app-debug.apk');
        if (file_exists($sourcePath)) {
            copy($sourcePath, $apkPath);
        } else {
            return response()->json(['error' => 'APK file not found. Please build the mobile application or configure APK_DOWNLOAD_URL in your environment.'], 404);
        }
    }
    return response()->download($apkPath, 'AI-Looping.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Cache-Control' => 'no-cache, must-revalidate',
    ]);
})->name('apk.download');

Route::get('/app-download', function () {
    $apkPath = public_path('app-debug.apk');
    $fileSizeMb = file_exists($apkPath) ? round(filesize($apkPath) / (1024 * 1024), 1) : 0;
    $downloadUrl = env('APK_DOWNLOAD_URL') ?: route('apk.download');
    $currentHost = request()->getHttpHost();
    return view('download_app', compact('downloadUrl', 'fileSizeMb', 'currentHost'));
})->name('app.download.page');

// --- Contact Us & Inbound Inquiries ---
Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'show'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');

// --- Legal & Production Compliance Pages ---
Route::get('/terms', [\App\Http\Controllers\LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [\App\Http\Controllers\LegalController::class, 'privacy'])->name('privacy');
Route::get('/cookies', [\App\Http\Controllers\LegalController::class, 'cookies'])->name('cookies');

// --- Temporary Remote Database Migration Route (For Vercel / Aiven MySQL) ---
// Visit: /migrate?secret=ailoop_migrate_2026 (or custom MIGRATION_SECRET from .env)
Route::match(['get', 'post'], '/migrate', function (\Illuminate\Http\Request $request) {
    $expectedSecret = env('MIGRATION_SECRET', 'ailoop_migrate_2026');
    $providedSecret = $request->input('secret') ?? $request->query('secret');

    if ($providedSecret !== $expectedSecret) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. Please supply the valid secret token: ?secret=YOUR_SECRET (Default: ?secret=ailoop_migrate_2026)',
        ], 403);
    }

    $action = $request->query('action', 'migrate');

    try {
        // Test database connection first
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbPrefix = config('database.connections.mysql.prefix');

        switch ($action) {
            case 'status':
                \Illuminate\Support\Facades\Artisan::call('migrate:status');
                $message = 'Migration status check completed.';
                break;
            case 'migrate':
            default:
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $message = 'Migrations executed successfully with prefix: [' . ($dbPrefix ?: 'none') . ']';
                break;
        }

        $artisanOutput = \Illuminate\Support\Facades\Artisan::output();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'database' => [
                    'host' => $dbHost,
                    'database' => $dbName,
                    'prefix' => $dbPrefix,
                ],
                'output' => $artisanOutput,
            ]);
        }

        return response("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>Database Migration Console - AI Looping</title>
                <style>
                    body { font-family: ui-monospace, Menlo, Monaco, Consolas, monospace; background: #0f172a; color: #f8fafc; padding: 2rem; margin: 0; }
                    .card { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                    .badge { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: bold; background: #10b981; color: white; margin-bottom: 1rem; }
                    .info { background: #0f172a; padding: 1rem; border-radius: 8px; font-size: 0.9rem; margin-bottom: 1.5rem; border: 1px solid #334155; line-height: 1.6; }
                    pre { background: #020617; padding: 1rem; border-radius: 8px; overflow-x: auto; color: #38bdf8; font-size: 0.88rem; line-height: 1.5; border: 1px solid #1e293b; }
                    .btn { display: inline-block; padding: 0.5rem 1rem; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-right: 0.5rem; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <span class='badge'>✓ Migration Console</span>
                    <h2>Database Operations Output</h2>
                    <div class='info'>
                        <strong>Host:</strong> {$dbHost}<br>
                        <strong>Database:</strong> {$dbName}<br>
                        <strong>Table Prefix:</strong> <code>{$dbPrefix}</code> (Isolates AI Looping from other applications)<br>
                        <strong>Status:</strong> {$message}
                    </div>
                    <h3>Artisan Console Log:</h3>
                    <pre>" . htmlspecialchars($artisanOutput ?: 'Nothing to migrate or command completed with no output.') . "</pre>
                    <div style='margin-top: 1.5rem;'>
                        <a href='?secret=" . urlencode($providedSecret) . "&action=migrate' class='btn'>Run Migrate</a>
                        <a href='?secret=" . urlencode($providedSecret) . "&action=status' class='btn' style='background: #0284c7;'>Check Status</a>
                        <a href='/' class='btn' style='background: #475569;'>Go to Website &rarr;</a>
                    </div>
                </div>
            </body>
            </html>
        ", 200, ['Content-Type' => 'text/html']);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => 'Database operation failed',
            'message' => $e->getMessage(),
            'connection' => [
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
                'prefix' => config('database.connections.mysql.prefix'),
            ],
            'file' => $e->getFile() . ':' . $e->getLine(),
        ], 500);
    }
})->name('artisan.migrate');


