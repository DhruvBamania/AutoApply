<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedApplication;
use App\Services\ChromeExtensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChromeExtensionApiController extends Controller
{
    protected ChromeExtensionService $extensionService;

    public function __construct(ChromeExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * Check extension connection status and candidate readiness.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'connected' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'has_resume' => !empty($user->resume_text),
                'saved_applications_count' => $user->savedApplications()->count(),
                'tailored_resumes_count' => $user->tailoredResumes()->count(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Analyze job description against candidate resume in real time.
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_description' => 'required|string|min:20',
            'job_title' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:100',
            'job_url' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $analysis = $this->extensionService->analyzeJob($user, $validated);

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'analysis' => $analysis,
            'job' => [
                'title' => $validated['job_title'] ?? 'Role',
                'company' => $validated['company_name'] ?? 'Company',
                'platform' => $validated['platform'] ?? 'External Portal',
            ],
        ]);
    }

    /**
     * Generate customized answer to screening question on job application.
     */
    public function generateAnswer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|min:5|max:1000',
            'job_title' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'tone' => 'nullable|string|in:professional,concise,enthusiastic,technical',
        ]);

        $user = $request->user();
        $result = $this->extensionService->generateScreeningAnswer($user, $validated);

        return response()->json([
            'success' => true,
            'data' => [
                'question' => $validated['question'],
                'answer' => $result['answer'],
                'tone' => $validated['tone'] ?? 'professional',
                'highlights' => $result['key_highlights'] ?? [],
            ],
            'question' => $validated['question'],
            'answer' => $result['answer'],
            'highlights' => $result['key_highlights'] ?? [],
        ]);
    }

    /**
     * Provide candidate profile fields formatted for 1-click form auto-filling.
     */
    public function autofillProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $this->extensionService->getAutofillProfile($user);

        return response()->json([
            'success' => true,
            'data' => $profile,
            'profile' => $profile,
        ]);
    }

    /**
     * Save an external job to the AutoApply Application Tracker.
     */
    public function saveJob(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:100',
            'job_type' => 'nullable|string|max:100',
            'job_url' => 'nullable|string|max:2000',
            'platform' => 'nullable|string|max:100',
            'job_description' => 'nullable|string',
            'match_score' => 'nullable|integer|min:0|max:100',
            'match_analysis' => 'nullable|array',
            'status' => 'nullable|string|in:bookmarked,applied,interviewing,offered,rejected',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        // Check if application already exists for this URL or title+company
        $query = $user->savedApplications();
        if (!empty($validated['job_url'])) {
            $existing = $query->where('job_url', $validated['job_url'])->first();
        } else {
            $existing = $query->where('job_title', $validated['job_title'])
                ->where('company_name', $validated['company_name'])
                ->first();
        }

        $status = $validated['status'] ?? 'bookmarked';
        $appliedAt = ($status === 'applied' && (!$existing || !$existing->applied_at)) ? now() : ($existing->applied_at ?? null);

        $data = array_merge($validated, [
            'status' => $status,
            'applied_at' => $appliedAt,
            'platform' => $validated['platform'] ?? 'Other',
        ]);

        if ($existing) {
            $existing->update($data);
            $application = $existing;
            $message = 'Application updated in AutoApply Tracker.';
            $statusCode = 200;
        } else {
            $application = $user->savedApplications()->create($data);
            $message = 'Job saved to AutoApply Application Tracker.';
            $statusCode = 201;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $application,
            'application' => $application,
        ], $statusCode);
    }

    /**
     * List all tracked applications for the candidate.
     */
    public function applications(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->savedApplications();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('platform') && !empty($request->platform)) {
            $query->where('platform', $request->platform);
        }

        $applications = $query->latest('id')->paginate(15);

        // Compute summary counts
        $counts = [
            'total' => $user->savedApplications()->count(),
            'bookmarked' => $user->savedApplications()->where('status', 'bookmarked')->count(),
            'applied' => $user->savedApplications()->where('status', 'applied')->count(),
            'interviewing' => $user->savedApplications()->where('status', 'interviewing')->count(),
            'offered' => $user->savedApplications()->where('status', 'offered')->count(),
            'rejected' => $user->savedApplications()->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications->items(),
                'pagination' => $applications,
                'counts' => $counts,
            ],
            'applications' => $applications,
            'counts' => $counts,
        ]);
    }

    /**
     * Update application status or add notes.
     */
    public function updateApplication(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:bookmarked,applied,interviewing,offered,rejected',
            'notes' => 'nullable|string',
        ]);

        $application = $request->user()->savedApplications()->findOrFail($id);

        if ($validated['status'] === 'applied' && !$application->applied_at) {
            $application->applied_at = now();
        }

        $application->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated.',
            'data' => $application,
            'application' => $application,
        ]);
    }

    /**
     * Delete tracked application.
     */
    public function deleteApplication(Request $request, int $id): JsonResponse
    {
        $application = $request->user()->savedApplications()->findOrFail($id);
        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application removed from tracker.',
        ]);
    }
}

