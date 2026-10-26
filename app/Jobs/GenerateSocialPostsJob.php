<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SocialPostGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSocialPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(SocialPostGeneratorService $generator): void
    {
        if (empty($this->user->resume_text)) {
            Log::warning('GenerateSocialPostsJob skipped: User does not have resume text', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        try {
            Log::info('Generating social posts in background for candidate', [
                'user_id' => $this->user->id,
            ]);

            $posts = $generator->generateForAllPlatforms($this->user);

            Log::info('Candidate social posts generated successfully via background job', [
                'user_id' => $this->user->id,
                'platforms' => is_array($posts) ? array_keys($posts) : 'all',
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateSocialPostsJob failed', [
                'user_id' => $this->user->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
