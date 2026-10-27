<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    /**
     * Dedicated Public & Candidate Jobs directory page to discover and explore all posted jobs.
     */
    public function index(Request $request)
    {
        $query = JobPosting::with('user.companyProfile')
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('skills', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user.companyProfile', function ($sq) use ($search) {
                      $sq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        if ($request->filled('job_type') && $request->job_type !== 'all') {
            $query->where('job_type', $request->job_type);
        }

        $jobs = $query->latest()->paginate(12)->withQueryString();

        // Calculate metrics for candidates
        $totalActiveJobs = JobPosting::where('status', 'active')->count();
        $totalCompaniesHiring = \App\Models\CompanyProfile::whereHas('user.jobPostings', function ($q) {
            $q->where('status', 'active');
        })->count();
        $remoteJobsCount = JobPosting::where('status', 'active')
            ->where(function ($q) {
                $q->where('location', 'like', '%remote%')
                  ->orWhere('title', 'like', '%remote%');
            })->count();

        return view('public.jobs.index', compact('jobs', 'totalActiveJobs', 'totalCompaniesHiring', 'remoteJobsCount'));
    }

    /**
     * Public page for candidates viewing the job post (linked from LinkedIn, Naukri, Indeed).
     */
    public function show(Request $request, $id)
    {
        $job = JobPosting::with('user.companyProfile')
            ->where('status', 'active')
            ->findOrFail($id);

        $referralPlatform = $request->query('src', 'direct');
        if (!in_array($referralPlatform, ['linkedin', 'naukri', 'indeed', 'direct'])) {
            $referralPlatform = 'direct';
        }

        return view('public.jobs.show', compact('job', 'referralPlatform'));
    }

    /**
     * Submit a candidate inquiry, question, or application.
     */
    public function inquire(Request $request, $id)
    {
        $job = JobPosting::where('status', 'active')->findOrFail($id);

        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email|max:255',
            'candidate_phone' => 'nullable|string|max:25',
            'candidate_headline' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
            'type' => 'required|in:query,application,comment',
            'platform' => 'nullable|in:linkedin,naukri,indeed,direct',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('job_resumes', 'public');
        }

        $platform = $request->input('platform', 'direct');

        JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => $platform,
            'type' => $request->type,
            'candidate_name' => $request->candidate_name,
            'candidate_email' => $request->candidate_email,
            'candidate_phone' => $request->candidate_phone,
            'candidate_headline' => $request->candidate_headline,
            'message' => $request->message,
            'resume_path' => $resumePath,
            'status' => 'new',
        ]);

        $companyName = $job->user->companyProfile->company_name ?? $job->user->name;

        return back()->with('success', "Thank you! Your {$request->type} has been submitted directly to the hiring team at {$companyName}.");
    }

    /**
     * Candidate sends a direct WhatsApp message to the company hiring team via Twilio.
     */
    public function sendWhatsAppMessage(Request $request, $id, \App\Services\TwilioWhatsAppService $whatsAppService)
    {
        $job = JobPosting::with('user.companyProfile')->where('status', 'active')->findOrFail($id);

        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'candidate_phone' => 'required|string|max:25',
            'candidate_email' => 'nullable|email|max:255',
            'candidate_headline' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
        ]);

        $result = $whatsAppService->sendCandidateInquiry($job, $request->all());

        $companyName = $job->user->companyProfile->company_name ?? $job->user->name;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "WhatsApp message successfully delivered to the hiring team at {$companyName}!",
                'inquiry' => $result['inquiry'] ?? null,
                'status' => $result['status'] ?? 'sent',
            ]);
        }

        return back()->with('success', "💬 Your WhatsApp message has been successfully sent to {$companyName}'s hiring team!");
    }
}
