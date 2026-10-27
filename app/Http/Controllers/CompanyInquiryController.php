<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use App\Services\JobInquiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CandidateShortlistedMail;
use App\Jobs\SendShortlistEmailJob;

class CompanyInquiryController extends Controller
{
    protected JobInquiryService $inquiryService;

    public function __construct(JobInquiryService $inquiryService)
    {
        $this->inquiryService = $inquiryService;
    }

    /**
     * Display centralized inquiries and comments grouped job role wise.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedJobId = $request->query('job_id');
        $selectedPlatform = $request->query('platform');
        $selectedStatus = $request->query('status');
        $selectedType = $request->query('type');

        // All jobs belonging to this company
        $allJobs = $user->jobPostings()->latest()->get();

        // Query inquiries for jobs of this company
        $inquiriesQuery = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting')->orderBy('is_pinned', 'desc')->latest('created_at')->latest('id');

        if ($selectedJobId) {
            $inquiriesQuery->where('job_posting_id', $selectedJobId);
        }
        if ($selectedPlatform) {
            $inquiriesQuery->where('platform', $selectedPlatform);
        }
        if ($selectedStatus) {
            $inquiriesQuery->where('status', $selectedStatus);
        }
        if ($selectedType) {
            $inquiriesQuery->where('type', $selectedType);
        }

        $inquiries = $inquiriesQuery->get();

        // Group inquiries by job role for job-role-wise presentation
        $groupedInquiries = $inquiries->groupBy('job_posting_id');

        // Statistics
        $totalInquiries = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $newInquiriesCount = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'new')->count();

        $repliedCount = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'replied')->count();

        return view('company.inquiries.index', compact(
            'allJobs',
            'inquiries',
            'groupedInquiries',
            'totalInquiries',
            'newInquiriesCount',
            'repliedCount',
            'selectedJobId',
            'selectedPlatform',
            'selectedStatus',
            'selectedType'
        ));
    }

    /**
     * Trigger synchronization with external platform APIs (LinkedIn, Naukri, Indeed).
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $jobId = $request->input('job_id');

        if ($jobId) {
            $job = $user->jobPostings()->findOrFail($jobId);
            $result = $this->inquiryService->syncJobInquiries($job);
            $msg = "Synced {$result['total']} new item(s) for '{$job->title}' (LinkedIn: {$result['linkedin']}, Naukri: {$result['naukri']}, Indeed: {$result['indeed']}).";
        } else {
            $result = $this->inquiryService->syncAllForCompany($user);
            $msg = "Sync complete across {$result['jobs_checked']} job roles. {$result['total']} new comment(s) and inquiries retrieved.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Manually log a comment or inquiry from LinkedIn, Naukri, or Indeed.
     */
    public function storeManual(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'job_posting_id' => 'required|exists:job_postings,id',
            'platform' => 'required|in:linkedin,naukri,indeed,direct',
            'candidate_name' => 'required|string|max:255',
            'candidate_headline' => 'nullable|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'message' => 'required|string|max:3000',
        ]);

        $job = $user->jobPostings()->findOrFail($request->job_posting_id);

        JobInquiry::create([
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

        return back()->with('success', "Comment by '{$request->candidate_name}' logged under '{$job->title}' successfully!");
    }

    /**
     * Update inquiry review status (e.g. new, under_review, shortlisted, rejected).
     * If shortlisted, sets interview schedule and dispatches automated candidate email.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

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
                Log::error("Failed to dispatch candidate shortlist email job: " . $e->getMessage());
            }
        }

        $message = "Status updated to '" . ucfirst(str_replace('_', ' ', $request->status)) . "'.";
        if ($emailSent) {
            $message .= " ✉️ Automated interview invitation email sent to {$inquiry->candidate_email}!";
        } elseif ($request->status === 'shortlisted' && empty($inquiry->candidate_email)) {
            $message .= " (Note: Candidate does not have an email address on file).";
        }

        return back()->with('success', $message);
    }

    /**
     * Send employer reply to the candidate inquiry / comment.
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'company_reply' => 'required|string|max:2000',
        ]);

        $this->inquiryService->replyToInquiry($inquiry, $request->company_reply);

        return back()->with('success', "Reply recorded and sent to {$inquiry->candidate_name}.");
    }

    /**
     * Generate realistic sample comments & inquiries across platforms for testing without external accounts.
     */
    public function seedDemo(Request $request)
    {
        $user = Auth::user();
        $job = $request->input('job_id') 
            ? $user->jobPostings()->find($request->input('job_id')) 
            : $user->jobPostings()->latest()->first();

        if (!$job) {
            return back()->with('error', 'Please post at least one job role first before seeding sample comments and inquiries.');
        }

        $samples = [
            [
                'job_posting_id' => $job->id,
                'platform' => 'linkedin',
                'type' => 'comment',
                'candidate_name' => 'Priya Sharma',
                'candidate_email' => 'priya.sharma@example.com',
                'candidate_headline' => 'Senior Full-Stack Engineer at CloudCorp',
                'message' => 'Exciting role! Does your team consider fully remote candidates or is hybrid mandatory in Bengaluru?',
                'external_id' => 'sample_linkedin_' . uniqid(),
                'status' => 'new',
                'created_at' => now()->subHours(2),
            ],
            [
                'job_posting_id' => $job->id,
                'platform' => 'naukri',
                'type' => 'application',
                'candidate_name' => 'Rahul Verma',
                'candidate_email' => 'rahul.verma@example.com',
                'candidate_headline' => 'Backend Laravel Developer | 4.5 Yrs Exp',
                'message' => 'Applied through Naukri.com. I have experience building scalable REST APIs and queue pipelines. Looking forward to discussing this opportunity.',
                'external_id' => 'sample_naukri_' . uniqid(),
                'status' => 'new',
                'created_at' => now()->subHours(5),
            ],
            [
                'job_posting_id' => $job->id,
                'platform' => 'indeed',
                'type' => 'query',
                'candidate_name' => 'Ananya Patel',
                'candidate_email' => 'ananya.patel@example.com',
                'candidate_headline' => 'Software Engineer | Microservices Specialist',
                'message' => 'Hello team, what is the expected notice period and interview process timeline for this role?',
                'external_id' => 'sample_indeed_' . uniqid(),
                'status' => 'new',
                'created_at' => now()->subMinutes(40),
            ],
        ];

        foreach ($samples as $sample) {
            JobInquiry::create($sample);
        }

        return back()->with('success', "🧪 Seeded 3 realistic sample comments & queries (LinkedIn, Naukri, Indeed) for '{$job->title}'!");
    }

    /**
     * Toggle pinning/unpinning of a candidate inquiry or comment.
     */
    public function togglePin(Request $request, $id)
    {
        $user = Auth::user();
        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $inquiry->is_pinned = !$inquiry->is_pinned;
        $inquiry->save();

        $statusMsg = $inquiry->is_pinned 
            ? "📌 Inquiry from '{$inquiry->candidate_name}' pinned to the top!" 
            : "Inquiry from '{$inquiry->candidate_name}' unpinned.";

        return back()->with('success', $statusMsg);
    }

    /**
     * Send employer reply to the candidate inquiry via Twilio WhatsApp.
     */
    public function replyWhatsApp(Request $request, $id, \App\Services\TwilioWhatsAppService $whatsAppService)
    {
        $user = Auth::user();
        $inquiry = JobInquiry::whereHas('jobPosting', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

        $replyContent = $request->input('company_reply', $request->input('reply_message'));
        if (empty($replyContent)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'The company reply field is required.'], 422);
            }
            return back()->with('error', 'The company reply field is required.');
        }

        if ($request->filled('candidate_phone')) {
            $inquiry->candidate_phone = $request->candidate_phone;
            $inquiry->save();
        }

        if (empty($inquiry->candidate_phone)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Candidate has not provided a phone number for WhatsApp messaging.'], 422);
            }
            return back()->with('error', 'Candidate has not provided a phone number for WhatsApp messaging.');
        }

        $result = $whatsAppService->sendCompanyReply($inquiry, $replyContent, $user);

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $result['error'] ?? 'Failed to send WhatsApp message.'], 400);
            }
            return back()->with('error', $result['error'] ?? 'Failed to send WhatsApp message.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "WhatsApp message successfully delivered to {$inquiry->candidate_name}!",
                'inquiry' => $inquiry->fresh(),
            ]);
        }

        return back()->with('success', "💬 WhatsApp reply successfully delivered to {$inquiry->candidate_name}!");
    }
}
