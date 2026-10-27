<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Services\JobPostingDistributorService;
use App\Services\CandidateMatcherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyJobController extends Controller
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

    public function index()
    {
        $jobs = Auth::user()->jobPostings()->latest()->get();
        $connectedAccounts = Auth::user()->connectedAccounts->keyBy('platform');
        return view('company.jobs.index', compact('jobs', 'connectedAccounts'));
    }

    public function create()
    {
        $company = Auth::user()->companyProfile;
        $connectedAccounts = Auth::user()->connectedAccounts->keyBy('platform');
        return view('company.jobs.create', compact('company', 'connectedAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'job_type' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'skills' => 'nullable|string|max:500',
            'whatsapp_number' => 'nullable|string|max:25',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'platforms' => 'nullable|array',
            'platforms.*' => 'in:linkedin,naukri,indeed',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('job_images', 'public');
        }

        $platforms = $request->input('platforms', ['linkedin', 'naukri', 'indeed']);

        // Idempotency guard: prevent duplicate job creation from rapid multiple clicks
        $recentDuplicate = Auth::user()->jobPostings()
            ->where('title', $request->title)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->latest()
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('company.jobs.show', $recentDuplicate->id)
                ->with('success', "Job '{$recentDuplicate->title}' is already being processed.");
        }

        $companyProfile = Auth::user()->companyProfile;
        $whatsappNumber = $request->input('whatsapp_number') ?: ($companyProfile->whatsapp_number ?? null);

        // If company profile didn't have WhatsApp number yet, save it as default
        if ($request->filled('whatsapp_number') && $companyProfile && empty($companyProfile->whatsapp_number)) {
            $companyProfile->whatsapp_number = $request->input('whatsapp_number');
            $companyProfile->save();
        }

        $job = Auth::user()->jobPostings()->create([
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

        // One-Click Distribution to all selected platforms
        $results = $this->distributor->distribute($job, $platforms);

        $failures = [];
        $successes = [];
        foreach ($results as $platform => $result) {
            if ($result['status'] === 'posted') {
                $successes[] = ucfirst($platform);
            } elseif ($result['status'] === 'failed') {
                $failures[] = ucfirst($platform) . ': ' . ($result['error'] ?? 'Posting failed');
            }
        }

        if (count($failures) > 0 && count($successes) > 0) {
            $msg = "Job '{$job->title}' posted to " . implode(', ', $successes) . ", but failed on: " . implode('; ', $failures);
            return redirect()->route('company.jobs.show', $job->id)->with('error', $msg);
        } elseif (count($failures) > 0) {
            $msg = "Job '{$job->title}' created, but distribution failed: " . implode('; ', $failures);
            return redirect()->route('company.jobs.show', $job->id)->with('error', $msg);
        }

        $platformNames = array_map('ucfirst', $platforms);
        $platformList = implode(', ', $platformNames);

        return redirect()->route('company.jobs.index')
            ->with('success', "Job '{$job->title}' successfully published and posted to {$platformList}!");
    }

    public function show($id)
    {
        $job = Auth::user()->jobPostings()->findOrFail($id);
        $connectedAccounts = Auth::user()->connectedAccounts->keyBy('platform');
        $inquiries = $job->inquiries;

        // Candidate ranking data
        $rankedCandidates = $job->inquiries()
            ->whereNotNull('ai_match_score')
            ->orderBy('ai_rank', 'asc')
            ->get();

        $topMatchesCount = $job->inquiries()->where('ai_match_score', '>=', 80)->count();
        $potentialMatchesCount = $job->inquiries()->whereBetween('ai_match_score', [60, 79])->count();
        $shortlistedCount = $job->inquiries()->where('status', 'shortlisted')->count();

        // AI Headhunter Matches
        $headhunterMatches = $job->headhunterMatches;

        return view('company.jobs.show', compact(
            'job',
            'connectedAccounts',
            'inquiries',
            'rankedCandidates',
            'topMatchesCount',
            'potentialMatchesCount',
            'shortlistedCount',
            'headhunterMatches'
        ));
    }

    /**
     * Run Gemini AI Candidate Matching & Ranking on resumes for this job post.
     */
    public function matchCandidates(Request $request, $id)
    {
        $job = Auth::user()->jobPostings()->findOrFail($id);
        $ranked = $this->matcher->matchAndRankCandidates($job);

        if ($ranked->isEmpty()) {
            return redirect()->route('company.jobs.show', $job->id)
                ->with('error', 'No candidate applications or inquiries found on this job post to analyze. Share your job link or load demo applications first!');
        }

        return redirect()->route('company.jobs.show', $job->id . '#ai-matching-section')
            ->with('success', "✨ Gemini AI has analyzed and ranked {$ranked->count()} candidate(s) for '{$job->title}'!");
    }

    /**
     * Batch shortlist top matching candidates (Score >= threshold).
     */
    public function batchShortlist(Request $request, $id)
    {
        $job = Auth::user()->jobPostings()->findOrFail($id);
        $threshold = (int)$request->input('threshold', 75);
        $count = $this->matcher->shortlistTopMatches($job, $threshold);

        if ($count === 0) {
            return redirect()->route('company.jobs.show', $job->id . '#ai-matching-section')
                ->with('error', "No candidates found with an AI Match Score ≥ {$threshold}% awaiting shortlisting.");
        }

        return redirect()->route('company.jobs.show', $job->id . '#ai-matching-section')
            ->with('success', "⭐ {$count} top-matching candidate(s) (Score ≥ {$threshold}%) have been shortlisted!");
    }

    public function repost(Request $request, $id)
    {
        $job = Auth::user()->jobPostings()->findOrFail($id);

        $platforms = $request->input('platforms', ['linkedin', 'naukri', 'indeed']);
        $results = $this->distributor->distribute($job, $platforms);

        $failures = [];
        $successes = [];
        foreach ($results as $platform => $result) {
            if ($result['status'] === 'posted') {
                $successes[] = ucfirst($platform);
            } elseif ($result['status'] === 'failed') {
                $failures[] = ucfirst($platform) . ': ' . ($result['error'] ?? 'Posting failed');
            }
        }

        if (count($failures) > 0) {
            $msg = "Re-posting attempted: " . (count($successes) ? "Posted to " . implode(', ', $successes) . ". " : "") . "Failed on: " . implode('; ', $failures);
            return back()->with('error', $msg);
        }

        return back()->with('success', "Job '{$job->title}' successfully re-posted to selected platforms!");
    }

    public function destroy($id)
    {
        $job = Auth::user()->jobPostings()->findOrFail($id);
        if ($job->image_path) {
            Storage::disk('public')->delete($job->image_path);
        }
        $job->delete();

        return redirect()->route('company.jobs.index')
            ->with('success', 'Job posting deleted successfully.');
    }
}
