<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Services\CandidateMatcherService;
use Illuminate\Http\Request;

class CompanyCandidateMatchApiController extends Controller
{
    protected CandidateMatcherService $matcher;

    public function __construct(CandidateMatcherService $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Trigger Gemini AI Candidate Matching & Ranking on resumes for this job post.
     */
    public function match(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);

        if ($request->boolean('async')) {
            \App\Jobs\ProcessAiCandidateMatchingJob::dispatch($job);
            return response()->json([
                'success' => true,
                'status' => 'queued',
                'message' => "Gemini AI candidate matching task queued in background for '{$job->title}'.",
                'job_id' => $job->id,
            ], 202);
        }

        $ranked = $this->matcher->matchAndRankCandidates($job);

        return response()->json([
            'success' => true,
            'message' => "Gemini AI evaluated and ranked {$ranked->count()} candidate(s) for '{$job->title}'.",
            'job_id' => $job->id,
            'last_ai_match_at' => $job->fresh()->last_ai_match_at,
            'total_ranked' => $ranked->count(),
            'leaderboard' => $ranked->map(function ($inq) {
                return [
                    'rank' => $inq->ai_rank,
                    'inquiry_id' => $inq->id,
                    'candidate_name' => $inq->candidate_name,
                    'candidate_email' => $inq->candidate_email,
                    'candidate_headline' => $inq->candidate_headline,
                    'platform' => $inq->platform,
                    'type' => $inq->type,
                    'resume_path' => $inq->resume_path ? asset('storage/' . $inq->resume_path) : null,
                    'has_resume' => !empty($inq->resume_path),
                    'match_score' => $inq->ai_match_score,
                    'experience_score' => $inq->ai_experience_score,
                    'skills_score' => $inq->ai_skills_score,
                    'projects_score' => $inq->ai_projects_score,
                    'recommendation' => $inq->ai_recommendation,
                    'summary' => $inq->ai_summary,
                    'matched_skills' => $inq->ai_matched_skills ?? [],
                    'missing_skills' => $inq->ai_missing_skills ?? [],
                    'status' => $inq->status,
                    'is_pinned' => (bool)$inq->is_pinned,
                ];
            })->values(),
        ]);
    }

    /**
     * Get candidate leaderboard for this job.
     */
    public function leaderboard(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $rankedCandidates = $job->inquiries()
            ->whereNotNull('ai_match_score')
            ->orderBy('ai_rank', 'asc')
            ->get();
        $stats = $job->inquiries()
            ->selectRaw("
                SUM(CASE WHEN ai_match_score >= 80 THEN 1 ELSE 0 END) as top_matches,
                SUM(CASE WHEN ai_match_score >= 60 AND ai_match_score < 80 THEN 1 ELSE 0 END) as potential_matches,
                SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted
            ")
            ->first();

        return response()->json([
            'success' => true,
            'job_id' => $job->id,
            'job_title' => $job->title,
            'last_ai_match_at' => $job->last_ai_match_at,
            'metrics' => [
                'total_analyzed' => $rankedCandidates->count(),
                'top_matches' => (int)($stats->top_matches ?? 0),
                'potential_matches' => (int)($stats->potential_matches ?? 0),
                'shortlisted' => (int)($stats->shortlisted ?? 0),
            ],
            'leaderboard' => $rankedCandidates->map(function ($inq) {
                return [
                    'rank' => $inq->ai_rank,
                    'inquiry_id' => $inq->id,
                    'candidate_name' => $inq->candidate_name,
                    'candidate_email' => $inq->candidate_email,
                    'candidate_headline' => $inq->candidate_headline,
                    'platform' => $inq->platform,
                    'type' => $inq->type,
                    'resume_path' => $inq->resume_path ? asset('storage/' . $inq->resume_path) : null,
                    'has_resume' => !empty($inq->resume_path),
                    'match_score' => $inq->ai_match_score,
                    'experience_score' => $inq->ai_experience_score,
                    'skills_score' => $inq->ai_skills_score,
                    'projects_score' => $inq->ai_projects_score,
                    'recommendation' => $inq->ai_recommendation,
                    'summary' => $inq->ai_summary,
                    'matched_skills' => $inq->ai_matched_skills ?? [],
                    'missing_skills' => $inq->ai_missing_skills ?? [],
                    'status' => $inq->status,
                    'is_pinned' => (bool)$inq->is_pinned,
                ];
            })->values(),
        ]);
    }

    /**
     * Batch shortlist top candidates (score >= threshold).
     */
    public function batchShortlist(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isCompany()) {
            return response()->json(['error' => 'Forbidden. Company role required.'], 403);
        }

        $job = $user->jobPostings()->findOrFail($id);
        $threshold = (int)$request->input('threshold', 75);
        $count = $this->matcher->shortlistTopMatches($job, $threshold);

        return response()->json([
            'success' => true,
            'message' => "{$count} candidate(s) scoring ≥ {$threshold}% moved to Shortlisted status.",
            'shortlisted_count' => $count,
        ]);
    }
}
