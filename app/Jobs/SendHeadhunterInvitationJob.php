<?php

namespace App\Jobs;

use App\Mail\HeadhunterInvitationMail;
use App\Models\HeadhunterMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHeadhunterInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected HeadhunterMatch $match;

    public function __construct(HeadhunterMatch $match)
    {
        $this->match = $match;
    }

    public function handle(): void
    {
        if (empty($this->match->candidate_email)) {
            Log::warning('SendHeadhunterInvitationJob skipped: Candidate email is missing', [
                'match_id' => $this->match->id,
            ]);
            return;
        }

        try {
            Mail::to($this->match->candidate_email)->send(
                new HeadhunterInvitationMail($this->match)
            );

            Log::info('Headhunter invitation email successfully dispatched', [
                'match_id' => $this->match->id,
                'candidate_email' => $this->match->candidate_email,
                'job_id' => $this->match->job_posting_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendHeadhunterInvitationJob failed on attempt', [
                'match_id' => $this->match->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
