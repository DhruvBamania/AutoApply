<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\SocialPost;
use App\Services\LeadGenerationService;
use App\Services\SocialPostGeneratorService;
use Illuminate\Http\Request;

class CandidateCampaignApiController extends Controller
{
    protected LeadGenerationService $leadService;
    protected SocialPostGeneratorService $socialPostService;

    public function __construct(
        LeadGenerationService $leadService,
        SocialPostGeneratorService $socialPostService
    ) {
        $this->leadService = $leadService;
        $this->socialPostService = $socialPostService;
    }

    /**
     * Get candidate's outbound campaigns with lead statistics.
     */
    public function campaigns(Request $request)
    {
        $user = $request->user();
        $campaigns = $user->campaigns()
            ->withCount('leads')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Create a new outbound campaign.
     */
    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_roles' => 'required|string|max:255',
            'target_locations' => 'nullable|string|max:255',
            'daily_limit' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();

        $campaign = $user->campaigns()->create([
            'name' => $request->name,
            'target_roles' => $request->target_roles,
            'target_locations' => $request->target_locations,
            'daily_limit' => $request->input('daily_limit', 10),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'campaign' => $campaign,
        ], 201);
    }

    /**
     * Generate leads & email drafts for campaign using Apollo API.
     */
    public function generateCampaignLeads(Request $request, $id)
    {
        $user = $request->user();
        $campaign = $user->campaigns()->findOrFail($id);

        $this->leadService->generateLeadsForCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Leads and tailored email drafts generated for campaign.',
            'campaign' => $campaign->load('leads.emailDraft'),
        ]);
    }

    /**
     * List AI social posts generated for candidate.
     */
    public function socialPosts(Request $request)
    {
        $user = $request->user();
        $posts = $user->socialPosts()->latest()->get();

        return response()->json([
            'success' => true,
            'posts' => $posts,
        ]);
    }

    /**
     * Generate new AI social posts across platforms (LinkedIn, Twitter, Facebook).
     */
    public function generateSocialPosts(Request $request)
    {
        $user = $request->user();
        if (empty($user->resume_text)) {
            return response()->json(['error' => 'Please upload a resume first.'], 400);
        }

        if ($request->boolean('async')) {
            \App\Jobs\GenerateSocialPostsJob::dispatch($user);
            return response()->json([
                'success' => true,
                'status' => 'queued',
                'message' => 'Social post generation queued in background.',
            ], 202);
        }

        $this->socialPostService->generateForAllPlatforms($user);

        return response()->json([
            'success' => true,
            'message' => 'Social posts generated successfully.',
            'posts' => $user->fresh()->socialPosts,
        ]);
    }

    /**
     * Approve and finalize an AI social post.
     */
    public function approveSocialPost(Request $request, $id)
    {
        $user = $request->user();
        $post = $user->socialPosts()->findOrFail($id);

        $post->update([
            'status' => 'approved',
            'content' => $request->input('content', $post->content),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social post approved.',
            'post' => $post,
        ]);
    }
}
