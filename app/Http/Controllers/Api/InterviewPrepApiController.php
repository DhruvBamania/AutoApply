<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterviewPreparation;
use App\Services\InterviewPrepService;
use Illuminate\Http\Request;

class InterviewPrepApiController extends Controller
{
    protected InterviewPrepService $prepService;

    public function __construct(InterviewPrepService $prepService)
    {
        $this->prepService = $prepService;
    }

    /**
     * Get all saved interview preparation sets for the authenticated candidate.
     */
    public function index(Request $request)
    {
        $preps = $request->user()->interviewPreparations()->latest()->get();

        return response()->json([
            'success' => true,
            'count' => $preps->count(),
            'data' => $preps,
        ]);
    }

    /**
     * Get a single interview preparation set with all topics & questions.
     */
    public function show(Request $request, int $id)
    {
        $prep = $request->user()->interviewPreparations()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $prep,
        ]);
    }

    /**
     * Generate dynamic AI interview preparation questions.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'role_title' => 'required|string|max:150',
            'experience_level' => 'required|string|max:100',
            'tech_stack' => 'required|string|max:500',
            'use_resume' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $resumeContext = $request->boolean('use_resume') ? $user->resume_text : null;

        $result = $this->prepService->generatePrep(
            $validated['role_title'],
            $validated['experience_level'],
            $validated['tech_stack'],
            $resumeContext
        );

        $prep = $user->interviewPreparations()->create([
            'role_title' => $result['role_title'] ?? $validated['role_title'],
            'experience_level' => $result['experience_level'] ?? $validated['experience_level'],
            'tech_stack' => $result['tech_stack'] ?? $validated['tech_stack'],
            'topics_data' => $result['topics'] ?? [],
            'total_questions' => $result['total_questions'] ?? 0,
            'bookmarked_ids' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Generated {$prep->total_questions} tailored interview questions across " . count($prep->topics_data) . " topics!",
            'data' => $prep,
        ]);
    }

    /**
     * Toggle bookmark state for a specific question.
     */
    public function toggleBookmark(Request $request, int $id)
    {
        $request->validate([
            'question_id' => 'required|string',
        ]);

        $prep = $request->user()->interviewPreparations()->findOrFail($id);
        $isBookmarked = $prep->toggleBookmark($request->question_id);

        return response()->json([
            'success' => true,
            'is_bookmarked' => $isBookmarked,
            'bookmarked_ids' => $prep->fresh()->bookmarked_ids ?? [],
        ]);
    }

    /**
     * Delete an interview preparation guide.
     */
    public function destroy(Request $request, int $id)
    {
        $prep = $request->user()->interviewPreparations()->findOrFail($id);
        $prep->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interview preparation guide removed successfully.',
        ]);
    }

    /**
     * Get candidate's extracted profile for 1-tap auto-fill.
     */
    public function resumeProfile(Request $request)
    {
        $user = $request->user();
        $role = '';
        $techStack = '';
        $experienceLevel = '1-3 years (Junior / Mid)';
        $hasResume = !empty($user->resume_text) || !empty($user->resume_json);

        if (!empty($user->resume_json)) {
            $json = is_string($user->resume_json) ? json_decode($user->resume_json, true) : $user->resume_json;

            if (!empty($json['basics']['label'])) {
                $role = $json['basics']['label'];
            } elseif (!empty($json['work'][0]['position'])) {
                $role = $json['work'][0]['position'];
            }

            if (!empty($json['skills']) && is_array($json['skills'])) {
                $skills = [];
                foreach ($json['skills'] as $s) {
                    if (is_string($s)) {
                        $skills[] = $s;
                    } elseif (is_array($s) && !empty($s['name'])) {
                        $skills[] = $s['name'];
                        if (!empty($s['keywords']) && is_array($s['keywords'])) {
                            $skills = array_merge($skills, $s['keywords']);
                        }
                    }
                }
                $techStack = implode(', ', array_unique(array_slice($skills, 0, 10)));
            }

            if (!empty($json['work']) && is_array($json['work'])) {
                $count = count($json['work']);
                if ($count >= 4) {
                    $experienceLevel = '5-8 years (Senior)';
                } elseif ($count >= 2) {
                    $experienceLevel = '3-5 years (Mid-Level)';
                } else {
                    $experienceLevel = '1-3 years (Junior)';
                }
            }
        } elseif (!empty($user->resume_text)) {
            $text = strtolower($user->resume_text);
            $commonTech = ['laravel', 'php', 'flutter', 'dart', 'react', 'node.js', 'python', 'mysql', 'docker', 'aws', 'vue.js', 'typescript', 'javascript', 'git'];
            $foundTech = [];
            foreach ($commonTech as $tech) {
                if (str_contains($text, $tech)) {
                    $foundTech[] = ucfirst($tech);
                }
            }
            if (!empty($foundTech)) {
                $techStack = implode(', ', $foundTech);
            }
        }

        return response()->json([
            'success' => true,
            'profile' => [
                'has_resume' => $hasResume,
                'role_title' => $role,
                'tech_stack' => $techStack,
                'experience_level' => $experienceLevel,
            ],
        ]);
    }
}
