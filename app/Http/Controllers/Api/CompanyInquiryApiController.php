<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobInquiry;
use App\Models\JobPosting;
use App\Services\JobInquiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CandidateShortlistedMail;
use App\Jobs\SendShortlistEmailJob;

class CompanyInquiryApiController extends Controller
{
    protected JobInquiryService $inquiryService;

    public function __construct(JobInquiryService $inquiryService)
    {
        $this->inquiryService = $inquiryService;
    }

    /**
     * Get candidate inquiries and comments grouped or filtered role-wise.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $userJobIds = $user->jobPostings()->pluck('id');

        $query = JobInquiry::whereIn('job_posting_id', $userJobIds)
            ->with('jobPosting:id,title,job_type,location');

        if ($request->filled('job_id')) {
            $query->where('job_posting_id', $request->job_id);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Pinned queries float to the top using composite index (job_posting_id, is_pinned, created_at)
        $inquiries = $query->orderBy('is_pinned', 'desc')->latest('created_at')->latest('id')->get();

        // Single optimized aggregation query instead of 4 separate queries
        $statsRaw = JobInquiry::whereIn('job_posting_id', $userJobIds)
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END), 0) as new_count,
                COALESCE(SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END), 0) as replied_count,
                COALESCE(SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END), 0) as shortlisted_count
            ")->first();

        $stats = [
            'total' => (int)($statsRaw->total ?? 0),
            'new' => (int)($statsRaw->new_count ?? 0),
            'replied' => (int)($statsRaw->replied_count ?? 0),
            'shortlisted' => (int)($statsRaw->shortlisted_count ?? 0),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'count' => $inquiries->count(),
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Toggle query pinning for an inquiry.
     */
    public function togglePin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $inquiry->is_pinned = !$inquiry->is_pinned;
        $inquiry->save();

        return response()->json([
            'success' => true,
            'message' => $inquiry->is_pinned ? "Inquiry pinned to top." : "Inquiry unpinned.",
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * Update review status (new, under_review, replied, shortlisted, rejected).
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'status' => 'required|in:new,under_review,replied,shortlisted,rejected',
            'interview_scheduled_at' => 'nullable|date',
            'meeting_type' => 'nullable|string|max:100',
            'meeting_link_or_location' => 'nullable|string|max:1000',
            'interview_notes' => 'nullable|string|max:2000',
            'send_email' => 'nullable',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'shortlisted') {
            $updateData['shortlisted_at'] = now();

            if ($request->filled('interview_scheduled_at')) {
                $updateData['interview_scheduled_at'] = $request->interview_scheduled_at;
            }
            if ($request->has('meeting_type')) {
                $updateData['meeting_type'] = $request->meeting_type;
            }
            if ($request->has('meeting_link_or_location')) {
                $updateData['meeting_link_or_location'] = $request->meeting_link_or_location;
            }
            if ($request->has('interview_notes')) {
                $updateData['interview_notes'] = $request->interview_notes;
            }
        }

        $inquiry->update($updateData);

        // Send automated email if shortlisted and candidate has an email
        $emailSent = false;
        $shouldSendEmail = $request->has('send_email') ? filter_var($request->send_email, FILTER_VALIDATE_BOOLEAN) : true;

        if ($request->status === 'shortlisted' && !empty($inquiry->candidate_email) && $shouldSendEmail) {
            try {
                SendShortlistEmailJob::dispatchAfterResponse($inquiry);
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error("Failed to send candidate shortlist email (API): " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Status updated to " . ucfirst(str_replace('_', ' ', $request->status)) . ($emailSent ? " and invitation email sent." : "."),
            'email_sent' => $emailSent,
            'inquiry' => $inquiry->fresh(),
        ]);
    }

    /**
     * Send employer response to candidate inquiry or comment.
     */
    public function reply(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'company_reply' => 'required|string|max:3000',
        ]);

        $this->inquiryService->replyToInquiry($inquiry, $request->company_reply);

        return response()->json([
            'success' => true,
            'message' => "Reply recorded and sent to {$inquiry->candidate_name}.",
            'inquiry' => $inquiry->fresh(),
        ]);
    }

    /**
     * Sync comments & inquiries from connected platforms.
     */
    public function sync(Request $request)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $jobId = $request->input('job_id');
        if ($jobId) {
            $job = $user->jobPostings()->findOrFail($jobId);
            $result = $this->inquiryService->syncJobInquiries($job);
            $msg = "Synced {$result['total']} new item(s) for '{$job->title}'.";
        } else {
            $result = $this->inquiryService->syncAllForCompany($user);
            $msg = "Sync complete across {$result['jobs_checked']} job roles. {$result['total']} items retrieved.";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'sync_result' => $result,
        ]);
    }

    /**
     * Manually log comment or inquiry.
     */
    public function storeManual(Request $request)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $request->validate([
            'job_posting_id' => 'required|exists:job_postings,id',
            'platform' => 'required|in:linkedin,naukri,indeed,direct',
            'candidate_name' => 'required|string|max:255',
            'candidate_headline' => 'nullable|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'message' => 'required|string|max:3000',
        ]);

        $job = $user->jobPostings()->findOrFail($request->job_posting_id);

        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => $request->platform,
            'type' => 'comment',
            'candidate_name' => $request->candidate_name,
            'candidate_headline' => $request->candidate_headline ?: ucfirst($request->platform) . ' Commenter',
            'candidate_email' => $request->candidate_email,
            'message' => $request->message,
            'external_id' => 'manual_' . $request->platform . '_' . uniqid(),
            'status' => 'new',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Comment logged under '{$job->title}'.",
            'inquiry' => $inquiry,
        ], 201);
    }

    /**
     * Send employer WhatsApp reply to candidate via Twilio API.
     */
    public function replyWhatsApp(Request $request, $id, \App\Services\TwilioWhatsAppService $whatsAppService)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

        $replyContent = $request->input('company_reply', $request->input('reply_message'));
        if (empty($replyContent)) {
            return response()->json([
                'success' => false,
                'error' => 'The company reply field is required.',
            ], 422);
        }

        if ($request->filled('candidate_phone')) {
            $inquiry->candidate_phone = $request->candidate_phone;
            $inquiry->save();
        }

        if (empty($inquiry->candidate_phone)) {
            return response()->json([
                'success' => false,
                'error' => 'Candidate has not provided a phone number to message via WhatsApp.',
            ], 422);
        }

        $result = $whatsAppService->sendCompanyReply($inquiry, $replyContent, $user);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'WhatsApp dispatch failed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "WhatsApp reply sent to {$inquiry->candidate_name}!",
            'inquiry' => $inquiry->fresh(),
            'status' => $result['status'] ?? 'sent',
        ]);
    }
}
