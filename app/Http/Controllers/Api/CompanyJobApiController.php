<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Services\JobPostingDistributorService;
use App\Services\CandidateMatcherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyJobApiController extends Controller
{
    protected JobPostingDistributorService $distributor;
    protected CandidateMatcherService $matcher;

    public function __construct(
        JobPostingDistributorService $distributor,
        CandidateMatcherService $matcher
    ) {
        $this->distributor = $distributor;
        $this->matcher = $matcher;
    }

    /**
     * List all job postings for authenticated company.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $jobs = $user->jobPostings()
            ->withCount(['inquiries', 'inquiries as new_inquiries_count' => function ($q) {
                $q->where('status', 'new');
            }, 'inquiries as shortlisted_count' => function ($q) {
                $q->where('status', 'shortlisted');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total_jobs' => $jobs->count(),
            'jobs' => $jobs,
        ]);
    }

    /**
     * 1-Click Multi-Platform Job Posting with Flyer Image Upload.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'job_type' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'skills' => 'nullable|string',
            'description' => 'required|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'in:linkedin,naukri,indeed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('job_images', 'public');
        }

        // Idempotency guard: prevent duplicate job creation from rapid multiple calls
        $recentDuplicate = JobPosting::where('user_id', $user->id)
            ->where('title', $request->title)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->latest()
            ->first();

        if ($recentDuplicate) {
            return response()->json([
                'success' => true,
                'message' => "Job '{$recentDuplicate->title}' is already created.",
                'job' => $recentDuplicate,
            ], 200);
        }

        $companyProfile = $user->companyProfile;
        $whatsappNumber = $request->input('whatsapp_number') ?: ($companyProfile->whatsapp_number ?? null);

        if ($request->filled('whatsapp_number') && $companyProfile && empty($companyProfile->whatsapp_number)) {
            $companyProfile->whatsapp_number = $request->input('whatsapp_number');
            $companyProfile->save();
        }

        $job = JobPosting::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'job_type' => $request->job_type,
            'location' => $request->location,
            'salary_range' => $request->salary_range,
            'experience_required' => $request->experience_required,
            'skills' => $request->skills,
            'description' => $request->description,
            'image_path' => $imagePath,
            'whatsapp_number' => $whatsappNumber,
            'status' => 'active',
        ]);

        $platforms = $request->input('platforms', ['linkedin', 'naukri', 'indeed']);
        $distributionResults = $this->distributor->distribute($job, $platforms);

        return response()->json([
            'success' => true,
            'message' => "Job '{$job->title}' created and distributed to selected platforms.",
            'job' => $job->fresh(),
            'distribution_results' => $distributionResults,
        ], 201);
    }

    /**
     * Get single job details, platform statuses, and AI match metrics.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $connectedAccounts = $user->connectedAccounts->keyBy('platform');

        // Pinned inquiries first, then latest
        $inquiries = $job->inquiries;

        $stats = $job->inquiries()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN ai_match_score >= 80 THEN 1 ELSE 0 END) as top_matches,
                SUM(CASE WHEN ai_match_score >= 60 AND ai_match_score < 80 THEN 1 ELSE 0 END) as potential_matches,
                SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted
            ")
            ->first();

        return response()->json([
            'success' => true,
            'job' => $job,
            'connected_accounts' => $connectedAccounts,
            'total_inquiries' => (int)($stats->total ?? 0),
            'metrics' => [
                'top_matches' => (int)($stats->top_matches ?? 0),
                'potential_matches' => (int)($stats->potential_matches ?? 0),
                'shortlisted' => (int)($stats->shortlisted ?? 0),
            ],
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Re-post job to selected platforms.
     */
    public function repost(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $platforms = $request->input('platforms', ['linkedin', 'naukri', 'indeed']);
        $results = $this->distributor->distribute($job, $platforms);

        return response()->json([
            'success' => true,
            'message' => "Re-posting attempted for '{$job->title}'.",
            'job' => $job->fresh(),
            'results' => $results,
        ]);
    }

    /**
     * Delete a job posting.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        if ($job->image_path) {
            Storage::disk('public')->delete($job->image_path);
        }
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job posting deleted successfully.',
        ]);
    }
}
