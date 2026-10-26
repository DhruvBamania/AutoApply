<?php

namespace App\Http\Controllers;

use App\Models\HeadhunterMatch;
use App\Models\JobPosting;
use App\Services\AiHeadhunterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyHeadhunterController extends Controller
{
    protected AiHeadhunterService $headhunterService;

    public function __construct(AiHeadhunterService $headhunterService)
    {
        $this->headhunterService = $headhunterService;
    }

    /**
     * Trigger AI Headhunter Agent to scout candidates for this job posting.
     */
    public function scout(Request $request, $id)
    {
        $user = Auth::user();
        $job = $user->jobPostings()->findOrFail($id);

        $limit = (int)$request->input('limit', 6);
        $matches = $this->headhunterService->sourceCandidatesForJob($job, $limit);

        if ($matches->isEmpty()) {
            return redirect()->to(route('company.jobs.show', $job->id) . '#headhunter-section')
                ->with('error', 'No new potential candidates found in the pool for this role at this time.');
        }

        return redirect()->to(route('company.jobs.show', $job->id) . '#headhunter-section')
            ->with('success', "🎯 AI Headhunter Agent scouted and ranked {$matches->count()} standout candidate(s) for '{$job->title}'!");
    }

    /**
     * Send outreach invitation email to a specific sourced candidate.
     */
    public function invite(Request $request, $id)
    {
        $user = Auth::user();

        $match = HeadhunterMatch::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting')->findOrFail($id);

        $request->validate([
            'outreach_subject' => 'nullable|string|max:255',
            'outreach_message' => 'nullable|string|max:4000',
        ]);

        $this->headhunterService->sendInvitation(
            $match,
            $request->input('outreach_subject'),
            $request->input('outreach_message')
        );

        return redirect()->to(route('company.jobs.show', $match->job_posting_id) . '#headhunter-section')
            ->with('success', "✉️ Headhunter invitation sent to {$match->candidate_name} ({$match->candidate_email}) successfully!");
    }

    /**
     * Batch send invitations to all sourced candidates above score threshold.
     */
    public function batchInvite(Request $request, $id)
    {
        $user = Auth::user();
        $job = $user->jobPostings()->findOrFail($id);

        $minScore = (int)$request->input('min_score', 80);
        $count = $this->headhunterService->batchSendInvitations($job, $minScore);

        if ($count === 0) {
            return redirect()->to(route('company.jobs.show', $job->id) . '#headhunter-section')
                ->with('error', "No uninvited candidates found with a match score ≥ {$minScore}%.");
        }

        return redirect()->to(route('company.jobs.show', $job->id) . '#headhunter-section')
            ->with('success', "🚀 Successfully dispatched invitations to {$count} top candidate(s) (Match Score ≥ {$minScore}%)!");
    }
}
