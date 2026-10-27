<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeadhunterMatch;
use App\Models\JobPosting;
use App\Services\AiHeadhunterService;
use Illuminate\Http\Request;

class HeadhunterApiController extends Controller
{
    protected AiHeadhunterService $headhunterService;

    public function __construct(AiHeadhunterService $headhunterService)
    {
        $this->headhunterService = $headhunterService;
    }

    /**
     * Get sourced candidates for a company's job.
     */
    public function jobMatches(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);

        $matches = $job->headhunterMatches()
            ->with('candidateUser')
            ->orderBy('match_score', 'desc')
            ->get();

        $stats = [
            'total_sourced' => $matches->count(),
            'total_invited' => $matches->where('status', 'invited')->count(),
            'total_accepted' => $matches->where('status', 'accepted')->count(),
            'total_declined' => $matches->where('status', 'declined')->count(),
            'last_scout_at' => $job->last_headhunter_scout_at,
        ];

        $matchesList = $matches->map(function ($m) {
            return [
                'id' => $m->id,
                'job_posting_id' => $m->job_posting_id,
                'candidate_user_id' => $m->candidate_user_id,
                'candidate_name' => $m->candidate_name,
                'candidate_email' => $m->candidate_email,
                'candidate_headline' => $m->candidate_headline,
                'current_title' => $m->candidate_headline,
                'candidate_location' => $m->candidate_location,
                'location' => $m->candidate_location,
                'source_platform' => $m->source_platform,
                'pool_source' => $m->pool_source,
                'external_profile_url' => $m->external_profile_url,
                'summary_bio' => $m->summary_bio,
                'skills' => $m->skills ?? [],
                'match_score' => $m->match_score,
                'ai_match_score' => $m->match_score,
                'match_reasons' => $m->match_reasons ?? [],
                'ai_reasoning' => $m->ai_reasoning,
                'outreach_subject' => $m->outreach_subject,
                'outreach_message' => $m->outreach_message,
                'personalized_pitch' => $m->outreach_message,
                'status' => $m->status,
                'invited_at' => $m->invited_at ? $m->invited_at->toIso8601String() : null,
                'responded_at' => $m->responded_at ? $m->responded_at->toIso8601String() : null,
                'candidate_response_note' => $m->candidate_response_note,
            ];
        });

        return response()->json([
            'success' => true,
            'job_id' => $job->id,
            'job_title' => $job->title,
            'stats' => $stats,
            'data' => $matchesList,
            'matches' => $matchesList,
        ]);
    }

    /**
     * Trigger AI Headhunter Agent to scout candidates.
     */
    public function scout(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $limit = (int)$request->input('limit', 6);

        $matches = $this->headhunterService->sourceCandidatesForJob($job, $limit);

        return response()->json([
            'success' => true,
            'message' => "AI Headhunter scouted {$matches->count()} potential candidate(s) for '{$job->title}'.",
            'job_id' => $job->id,
            'total_scouted' => $matches->count(),
            'data' => $matches->values(),
            'matches' => $matches->values(),
        ]);
    }

    /**
     * Send outreach invitation to a candidate.
     */
    public function invite(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

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

        $freshMatch = $match->fresh();

        return response()->json([
            'success' => true,
            'message' => "Headhunter invitation sent to {$match->candidate_name}.",
            'data' => $freshMatch,
            'match' => $freshMatch,
        ]);
    }

    /**
     * Batch send outreach invitations.
     */
    public function batchInvite(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $minScore = (int)$request->input('min_score', 80);

        $count = $this->headhunterService->batchSendInvitations($job, $minScore);

        return response()->json([
            'success' => true,
            'message' => "Invitations dispatched to {$count} candidate(s) with score >= {$minScore}%.",
            'invited_count' => $count,
            'total_invited' => $count,
        ]);
    }

    /**
     * Get candidate's scouted invitations.
     */
    public function candidateInvitations(Request $request)
    {
        $user = $request->user();

        $invitations = HeadhunterMatch::where(function ($q) use ($user) {
            $q->where('candidate_user_id', $user->id)
              ->orWhere('candidate_email', $user->email);
        })
        ->whereIn('status', ['invited', 'accepted', 'declined'])
        ->with('jobPosting.user.companyProfile')
        ->latest('invited_at')
        ->get();

        $invitationsList = $invitations->map(function ($inv) {
            $job = $inv->jobPosting;
            $company = $job?->user?->companyProfile;

            return [
                'id' => $inv->id,
                'job_posting_id' => $job?->id,
                'job_id' => $job?->id,
                'job_title' => $job?->title,
                'company_name' => $company?->company_name ?? ($job?->user?->name ?? 'Confidential Company'),
                'company_website' => $company?->website,
                'job_location' => $job?->location,
                'job_type' => $job?->job_type,
                'salary_range' => $job?->salary_range,
                'job_salary' => $job?->salary_range,
                'match_score' => $inv->match_score,
                'ai_match_score' => $inv->match_score,
                'match_reasons' => $inv->match_reasons ?? [],
                'ai_reasoning' => $inv->ai_reasoning,
                'outreach_subject' => $inv->outreach_subject,
                'outreach_message' => $inv->outreach_message,
                'personalized_pitch' => $inv->outreach_message,
                'skills' => $inv->skills ?? [],
                'status' => $inv->status,
                'invited_at' => $inv->invited_at ? $inv->invited_at->toIso8601String() : null,
                'responded_at' => $inv->responded_at ? $inv->responded_at->toIso8601String() : null,
                'candidate_response_note' => $inv->candidate_response_note,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $invitationsList,
            'invitations' => $invitationsList,
        ]);
    }

    /**
     * Candidate accepts or declines headhunter invitation via API.
     */
    public function respondInvitation(Request $request, $id)
    {
        $user = $request->user();

        $match = HeadhunterMatch::where(function ($q) use ($user) {
            $q->where('candidate_user_id', $user->id)
              ->orWhere('candidate_email', $user->email);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

        $request->validate([
            'action' => 'required|in:accepted,declined',
            'note' => 'nullable|string|max:1000',
        ]);

        $this->headhunterService->respondToInvitation(
            $match,
            $request->action,
            $request->input('note')
        );

        $freshMatch = $match->fresh();

        return response()->json([
            'success' => true,
            'message' => $request->action === 'accepted' 
                ? 'Invitation accepted! You are fast-tracked into the employer shortlist.' 
                : 'Invitation declined.',
            'data' => $freshMatch,
            'invitation' => $freshMatch,
        ]);
    }

    /**
     * Send WhatsApp outreach message to a specific scouted candidate via Twilio API.
     */
    public function whatsAppOutreach(Request $request, $id, \App\Services\TwilioWhatsAppService $whatsAppService)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $match = HeadhunterMatch::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

        $phone = $request->input('candidate_phone', $request->input('recipient_phone'));
        $message = $request->input('outreach_message', $request->input('message'));

        if (!$phone || !$message) {
            return response()->json([
                'success' => false,
                'error' => 'Candidate phone number and message are required.',
            ], 422);
        }

        $result = $whatsAppService->sendHeadhunterOutreach(
            $match,
            $message,
            $user,
            $phone
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'WhatsApp dispatch failed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "WhatsApp outreach message delivered to {$match->candidate_name}!",
            'match' => $match->fresh(),
            'status' => $result['status'] ?? 'sent',
        ]);
    }
}
