<?php

namespace App\Jobs;

use App\Models\JobPosting;
use App\Services\CandidateMatcherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiCandidateMatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected JobPosting $job;

    public function __construct(JobPosting $job)
    {
        $this->job = $job;
    }

    public function handle(CandidateMatcherService $matcher): void
    {
        Log::info('Starting background Gemini AI candidate matching job', [
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
        ]);

        try {
            $ranked = $matcher->matchAndRankCandidates($this->job);

            Log::info('Gemini AI candidate matching completed successfully in background', [
                'job_id' => $this->job->id,
                'total_ranked' => $ranked->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Background Gemini AI candidate matching failed', [
                'job_id' => $this->job->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
