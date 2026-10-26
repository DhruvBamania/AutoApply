<?php

namespace App\Http\Controllers;

use App\Services\SocialPostGeneratorService;
use Illuminate\Http\Request;
use App\Models\SocialPost;

class SocialPostController extends Controller
{
    public function index()
    {
        $posts = auth()->user()->socialPosts->keyBy('platform');
        return view('social_posts.index', compact('posts'));
    }

    public function generate(Request $request, SocialPostGeneratorService $generator)
    {
        if (!auth()->user()->resume_text) {
            return redirect()->back()->with('error', 'Please upload a resume on the Dashboard first.');
        }

        if ($request->boolean('async')) {
            \App\Jobs\GenerateSocialPostsJob::dispatch(auth()->user());
            return redirect()->route('social_posts.index')->with('success', 'Social posts generation has been queued in the background!');
        }

        try {
            $generator->generateForAllPlatforms(auth()->user());
            return redirect()->route('social_posts.index')->with('success', 'Social posts generated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate posts: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $post = SocialPost::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate(['content' => 'required|string']);
        
        $post->update(['content' => $request->content]);
        
        return back()->with('success', ucfirst($post->platform) . ' post updated successfully.');
    }

    public function approve($id)
    {
        $post = SocialPost::where('user_id', auth()->id())->findOrFail($id);
        
        $post->update(['status' => 'approved']);
        
        if ($post->platform === 'linkedin') {
            \App\Jobs\LinkedInPosterJob::dispatch($post);
        } elseif ($post->platform === 'naukri') {
            \App\Jobs\NaukriAutomatorJob::dispatch($post);
        } elseif ($post->platform === 'indeed') {
            \App\Jobs\IndeedAutomatorJob::dispatch($post);
        }

        return back()->with('success', ucfirst($post->platform) . ' post approved and queued for dispatch!');
    }
}
