<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JobInquiryService
{
    /**
     * Sync comments, inquiries, and applications across platforms for a single job posting.
     */
    public function syncJobInquiries(JobPosting $job): array
    {
        $linkedinCount = $this->syncLinkedInComments($job);
        $naukriCount = $this->syncNaukriInquiries($job);
        $indeedCount = $this->syncIndeedInquiries($job);

        return [
            'total' => $linkedinCount + $naukriCount + $indeedCount,
            'linkedin' => $linkedinCount,
            'naukri' => $naukriCount,
            'indeed' => $indeedCount,
        ];
    }

    /**
     * Sync comments & inquiries across all job postings for a company.
     */
    public function syncAllForCompany(User $company): array
    {
        $totalSynced = 0;
        $jobs = $jobPostings = $company->jobPostings()->get();

        foreach ($jobs as $job) {
            $result = $this->syncJobInquiries($job);
            $totalSynced += $result['total'];
        }

        return [
            'total' => $totalSynced,
            'jobs_checked' => $jobs->count(),
        ];
    }

    /**
     * Fetch real comments from LinkedIn UGC Post via LinkedIn Social Actions API.
     */
    public function syncLinkedInComments(JobPosting $job): int
    {
        if (!$job->linkedin_post_id) {
            return 0;
        }

        $account = ConnectedAccount::where('user_id', $job->user_id)
            ->where('platform', 'linkedin')
            ->first();

        if (!$account || (!$account->access_token && $account->connection_type !== 'sandbox')) {
            return 0;
        }

        // Sandbox Simulation Mode
        if ($account->connection_type === 'sandbox') {
            if (!JobInquiry::where('job_posting_id', $job->id)->where('platform', 'linkedin')->exists()) {
                JobInquiry::create([
                    'job_posting_id' => $job->id,
                    'platform' => 'linkedin',
                    'type' => 'comment',
                    'candidate_name' => 'Kavita Menon (Sandbox)',
                    'candidate_headline' => 'Senior Full-Stack Engineer @ FinTech',
                    'message' => 'Great hiring announcement! Does this role offer flexible hybrid working hours?',
                    'external_id' => 'sandbox_li_' . uniqid(),
                    'status' => 'new',
                    'created_at' => now(),
                ]);
                return 1;
            }
            return 0;
        }

        try {
            $accessToken = decrypt($account->access_token);
            $activityUrn = $job->linkedin_post_id;
            $encodedUrn = rawurlencode($activityUrn);

            // Attempt 1: Modern REST socialActions with active version header
            $response = Http::timeout(12)->withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => '202609',
                    'X-Restli-Protocol-Version' => '2.0.0',
                ])
                ->get("https://api.linkedin.com/rest/socialActions/{$encodedUrn}/comments");

            // Attempt 2: Legacy v2 socialActions fallback
            if (!$response->successful()) {
                $responseV2 = Http::timeout(12)->withToken($accessToken)
                    ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                    ->get("https://api.linkedin.com/v2/socialActions/{$encodedUrn}/comments");
                if ($responseV2->successful()) {
                    $response = $responseV2;
                }
            }

            if (!$response->successful()) {
                if ($response->status() === 403) {
                    session()->flash('linkedin_sync_notice', 'LinkedIn API Notice: LinkedIn restricts automated reading of post comments to enterprise apps with Community Management Partner API approval (Error 403: ACCESS_DENIED). Candidates can use the application link in your post, or you can log external comments instantly using "+ Log LinkedIn Comment".');
                }
                Log::info("LinkedIn comments sync for [{$job->title}] (status {$response->status()}): " . $response->body());
                return 0;
            }

            $elements = $response->json('elements') ?? [];
            $newCount = 0;

            foreach ($elements as $element) {
                $commentUrn = $element['$URN'] ?? $element['id'] ?? null;
                $commentText = $element['message']['text'] ?? null;

                if (!$commentText) {
                    continue;
                }

                // If comment already exists in database, skip
                if ($commentUrn && JobInquiry::where('external_id', $commentUrn)->exists()) {
                    continue;
                }

                $actorUrn = $element['created']['actor'] ?? $element['actor'] ?? null;
                $actorName = 'LinkedIn Member';
                if ($actorUrn) {
                    $parts = explode(':', $actorUrn);
                    $suffix = end($parts);
                    $actorName = 'LinkedIn User #' . substr($suffix, 0, 8);
                }

                $createdAt = isset($element['created']['time'])
                    ? Carbon::createFromTimestampMs($element['created']['time'])
                    : now();

                JobInquiry::create([
                    'job_posting_id' => $job->id,
                    'platform' => 'linkedin',
                    'type' => 'comment',
                    'candidate_name' => $actorName,
                    'candidate_headline' => 'LinkedIn Network Member',
                    'message' => $commentText,
                    'external_id' => $commentUrn,
                    'status' => 'new',
                    'created_at' => $createdAt,
                ]);

                $newCount++;
            }

            return $newCount;

        } catch (\Throwable $e) {
            Log::error("LinkedIn comments sync failed for Job #{$job->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sync inquiries / applicants from Naukri RMS or recruiter gateway.
     */
    protected function syncNaukriInquiries(JobPosting $job): int
    {
        $account = ConnectedAccount::where('user_id', $job->user_id)
            ->where('platform', 'naukri')
            ->first();

        if (!$account) {
            return 0;
        }

        // Sandbox Simulation Mode
        if ($account->connection_type === 'sandbox') {
            if (!JobInquiry::where('job_posting_id', $job->id)->where('platform', 'naukri')->exists()) {
                JobInquiry::create([
                    'job_posting_id' => $job->id,
                    'platform' => 'naukri',
                    'type' => 'application',
                    'candidate_name' => 'Rahul Verma (Sandbox)',
                    'candidate_email' => 'rahul.verma@demo.com',
                    'candidate_headline' => 'Backend Laravel Developer | 4+ Yrs',
                    'message' => 'Applied via Naukri Recruiter portal. Excited about this opportunity.',
                    'external_id' => 'sandbox_naukri_' . uniqid(),
                    'status' => 'new',
                    'created_at' => now(),
                ]);
                return 1;
            }
            return 0;
        }

        try {
            if ($account->connection_type === 'api_key' || $account->access_token) {
                $apiKey = decrypt($account->access_token ?? $account->password);
                $response = Http::timeout(10)
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->get("https://rms.naukri.com/rms-gateway/api/v1/jobs/{$job->id}/applicants");

                if ($response->successful() && is_array($response->json('applicants'))) {
                    $count = 0;
                    foreach ($response->json('applicants') as $applicant) {
                        $extId = 'naukri_app_' . ($applicant['id'] ?? uniqid());
                        if (JobInquiry::where('external_id', $extId)->exists()) continue;

                        JobInquiry::create([
                            'job_posting_id' => $job->id,
                            'platform' => 'naukri',
                            'type' => 'application',
                            'candidate_name' => $applicant['name'] ?? 'Naukri Applicant',
                            'candidate_email' => $applicant['email'] ?? null,
                            'candidate_headline' => $applicant['experience'] ?? 'Naukri Job Seeker',
                            'message' => $applicant['query'] ?? 'Applied via Naukri.com with resume profile.',
                            'external_id' => $extId,
                            'status' => 'new',
                        ]);
                        $count++;
                    }
                    return $count;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Naukri inquiries sync error: " . $e->getMessage());
        }

        return 0;
    }

    /**
     * Sync inquiries / applicants from Indeed Employer API.
     */
    protected function syncIndeedInquiries(JobPosting $job): int
    {
        $account = ConnectedAccount::where('user_id', $job->user_id)
            ->where('platform', 'indeed')
            ->first();

        if (!$account) {
            return 0;
        }

        // Sandbox Simulation Mode
        if ($account->connection_type === 'sandbox') {
            if (!JobInquiry::where('job_posting_id', $job->id)->where('platform', 'indeed')->exists()) {
                JobInquiry::create([
                    'job_posting_id' => $job->id,
                    'platform' => 'indeed',
                    'type' => 'query',
                    'candidate_name' => 'Ananya Patel (Sandbox)',
                    'candidate_email' => 'ananya.patel@demo.com',
                    'candidate_headline' => 'Software Engineer | API Developer',
                    'message' => 'Inquiry from Indeed: What is the expected notice period for this vacancy?',
                    'external_id' => 'sandbox_indeed_' . uniqid(),
                    'status' => 'new',
                    'created_at' => now(),
                ]);
                return 1;
            }
            return 0;
        }

        try {
            if ($account->connection_type === 'api_key' || $account->access_token) {
                $token = decrypt($account->access_token ?? $account->password);
                $response = Http::timeout(10)
                    ->withToken($token)
                    ->get("https://api.indeed.com/v2/employer/jobs/{$job->id}/inquiries");

                if ($response->successful() && is_array($response->json('inquiries'))) {
                    $count = 0;
                    foreach ($response->json('inquiries') as $inq) {
                        $extId = 'indeed_inq_' . ($inq['id'] ?? uniqid());
                        if (JobInquiry::where('external_id', $extId)->exists()) continue;

                        JobInquiry::create([
                            'job_posting_id' => $job->id,
                            'platform' => 'indeed',
                            'type' => $inq['type'] ?? 'query',
                            'candidate_name' => $inq['name'] ?? 'Indeed Applicant',
                            'candidate_email' => $inq['email'] ?? null,
                            'candidate_headline' => $inq['headline'] ?? 'Indeed Candidate',
                            'message' => $inq['message'] ?? 'Inquiry regarding job opening.',
                            'external_id' => $extId,
                            'status' => 'new',
                        ]);
                        $count++;
                    }
                    return $count;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Indeed inquiries sync error: " . $e->getMessage());
        }

        return 0;
    }

    /**
     * Save an employer reply and send to candidate.
     */
    public function replyToInquiry(JobInquiry $inquiry, string $replyMessage): bool
    {
        $inquiry->update([
            'company_reply' => $replyMessage,
            'replied_at' => now(),
            'status' => 'replied',
        ]);

        // If from LinkedIn, attempt posting back as comment reply
        if ($inquiry->platform === 'linkedin' && $inquiry->external_id) {
            $this->postLinkedInReply($inquiry, $replyMessage);
        }

        return true;
    }

    /**
     * Post a reply comment back to LinkedIn if parent comment URN is available.
     */
    protected function postLinkedInReply(JobInquiry $inquiry, string $replyMessage): void
    {
        $job = $inquiry->jobPosting;
        if (!$job || !$job->linkedin_post_id) return;

        $account = ConnectedAccount::where('user_id', $job->user_id)
            ->where('platform', 'linkedin')
            ->first();

        if (!$account || !$account->access_token) return;

        try {
            $accessToken = decrypt($account->access_token);
            $meResponse = Http::timeout(8)->withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
            if (!$meResponse->successful()) return;

            $urn = 'urn:li:person:' . $meResponse->json('sub');
            $encodedUrn = rawurlencode($job->linkedin_post_id);

            Http::timeout(10)->withToken($accessToken)
                ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                ->post("https://api.linkedin.com/v2/socialActions/{$encodedUrn}/comments", [
                    'actor' => $urn,
                    'message' => ['text' => $replyMessage],
                    'parentComment' => $inquiry->external_id,
                ]);
        } catch (\Throwable $e) {
            Log::info("LinkedIn reply comment dispatch exception: " . $e->getMessage());
        }
    }
}
