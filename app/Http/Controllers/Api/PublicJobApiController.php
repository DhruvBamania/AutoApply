<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Http\Request;

class PublicJobApiController extends Controller
{
    /**
     * Browse active job openings for candidates.
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
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        $jobs = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'jobs' => $jobs,
        ]);
    }

    /**
     * View single active job opening details.
     */
    public function show($id)
    {
        $job = JobPosting::with('user.companyProfile')
            ->where('status', 'active')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'job' => $job,
        ]);
    }

    /**
     * Submit candidate application / inquiry with optional resume upload.
     */
    public function apply(Request $request, $id)
    {
        $job = JobPosting::where('status', 'active')->findOrFail($id);

        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email|max:255',
            'candidate_headline' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
            'type' => 'nullable|in:query,application,comment',
            'platform' => 'nullable|in:linkedin,naukri,indeed,direct',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('job_resumes', 'public');
        }

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => $request->input('platform', 'direct'),
            'type' => $request->input('type', 'application'),
            'candidate_name' => $request->candidate_name,
            'candidate_email' => $request->candidate_email,
            'candidate_headline' => $request->candidate_headline,
            'message' => $request->message,
            'resume_path' => $resumePath,
            'status' => 'new',
        ]);

        $companyName = $job->user->companyProfile->company_name ?? $job->user->name;

        return response()->json([
            'success' => true,
            'message' => "Application submitted successfully to {$companyName}.",
            'inquiry' => $inquiry,
        ], 201);
    }

    /**
     * Candidate sends direct WhatsApp message via Twilio API.
     */
    public function whatsappMessage(Request $request, $id, \App\Services\TwilioWhatsAppService $whatsAppService)
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

        return response()->json([
            'success' => true,
            'message' => "WhatsApp message successfully sent to {$companyName}!",
            'inquiry' => $result['inquiry'] ?? null,
            'status' => $result['status'] ?? 'sent',
        ], 201);
    }
}
