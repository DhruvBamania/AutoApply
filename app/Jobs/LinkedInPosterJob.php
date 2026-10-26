<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Models\ConnectedAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInPosterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected SocialPost $post;

    public function __construct(SocialPost $post)
    {
        $this->post = $post;
    }

    public function handle(): void
    {
        $account = ConnectedAccount::where('user_id', $this->post->user_id)
            ->where('platform', 'linkedin')
            ->first();

        if (!$account || !$account->access_token) {
            $this->post->update(['status' => 'failed']);
            Log::error('LinkedIn account not connected for user ' . $this->post->user_id);
            return;
        }

        try {
            $accessToken = decrypt($account->access_token);
            
            // First, get the LinkedIn Person URN (User ID)
            $meResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
            
            if (!$meResponse->successful()) {
                throw new \Exception('Failed to fetch LinkedIn profile: ' . $meResponse->body());
            }

            // Create a simple text share via UGC API
            $urn = 'urn:li:person:' . $meResponse->json('sub');

            $postResponse = Http::withToken($accessToken)
                ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                ->post('https://api.linkedin.com/v2/ugcPosts', [
                    'author' => $urn,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => [
                                'text' => $this->post->content
                            ],
                            'shareMediaCategory' => 'NONE'
                        ]
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                    ]
                ]);

            if ($postResponse->successful()) {
                $this->post->update(['status' => 'posted', 'posted_at' => now()]);
            } else {
                throw new \Exception('Failed to post to LinkedIn: ' . $postResponse->body());
            }

        } catch (\Exception $e) {
            $this->post->update(['status' => 'failed']);
            Log::error('LinkedIn posting error: ' . $e->getMessage());
        }
    }
}
