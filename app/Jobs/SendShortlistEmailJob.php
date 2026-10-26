<?php

namespace App\Jobs;

use App\Mail\CandidateShortlistedMail;
use App\Models\JobInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendShortlistEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected JobInquiry $inquiry;

    public function __construct(JobInquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function handle(): void
    {
        if (empty($this->inquiry->candidate_email)) {
            Log::warning('SendShortlistEmailJob skipped: Candidate email is missing', ['inquiry_id' => $this->inquiry->id]);
            return;
        }

        try {
            Mail::to($this->inquiry->candidate_email)->send(
                new CandidateShortlistedMail($this->inquiry, $this->inquiry->jobPosting)
            );
            Log::info('Candidate shortlisted notification email successfully dispatched via queue', [
                'inquiry_id' => $this->inquiry->id,
                'candidate_email' => $this->inquiry->candidate_email,
                'job_id' => $this->inquiry->job_posting_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendShortlistEmailJob failed on attempt', [
                'inquiry_id' => $this->inquiry->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
