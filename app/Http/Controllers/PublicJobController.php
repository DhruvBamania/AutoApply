<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
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
            'candidate_headline' => $request->candidate_headline,
            'message' => $request->message,
            'resume_path' => $resumePath,
            'status' => 'new',
        ]);

        $companyName = $job->user->companyProfile->company_name ?? $job->user->name;

        return back()->with('success', "Thank you! Your {$request->type} has been submitted directly to the hiring team at {$companyName}.");
    }
}
