<?php

namespace App\Http\Controllers;

use App\Models\InterviewPreparation;
use App\Models\MockInterviewSession;
use App\Services\MockInterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MockInterviewController extends Controller
{
    protected MockInterviewService $mockService;

    public function __construct(MockInterviewService $mockService)
    {
        $this->mockService = $mockService;
    }

    /**
     * Start a new interactive turn-by-turn mock interview session.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_title' => 'required|string|max:150',
            'experience_level' => 'required|string|max:100',
            'tech_stack' => 'required|string|max:500',
            'interview_preparation_id' => 'nullable|integer|exists:interview_preparations,id',
        ]);

        try {
            $user = $request->user();
            $session = $this->mockService->startSession($user, $validated);

            $history = $session->conversation_history ?? [];
            $lastMsg = !empty($history) ? end($history) : null;

            return response()->json([
                'status' => 'success',
                'session' => [
                    'id' => $session->id,
                    'role_title' => $session->role_title,
                    'experience_level' => $session->experience_level,
                    'tech_stack' => $session->tech_stack,
                    'current_question_index' => $session->current_question_index,
                    'total_questions' => $session->total_questions,
                    'status' => $session->status,
                    'conversation' => $session->conversation_history,
                    'latest_question' => $lastMsg['content'] ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('MockInterviewController start error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to launch mock interview simulator. Please try again.',
            ], 500);
        }
    }

    /**
     * Submit an answer to the current question turn.
     */
    public function submitAnswer(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'answer' => 'required|string|min:2|max:4000',
        ]);

        $session = $request->user()->mockInterviewSessions()->findOrFail($id);

        if ($session->isCompleted()) {
            return response()->json([
                'status' => 'completed',
                'message' => 'This interview session has already been completed.',
                'report' => $session->evaluation_report,
                'conversation' => $session->conversation_history,
            ]);
        }

        try {
            $result = $this->mockService->submitTurn($session, $validated['answer']);

            return response()->json([
                'status' => 'success',
                'turn' => $result,
                'session' => [
                    'id' => $session->id,
                    'current_question_index' => $session->current_question_index,
                    'total_questions' => $session->total_questions,
                    'status' => $session->status,
                    'conversation' => $session->conversation_history,
                    'evaluation_report' => $session->evaluation_report,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('MockInterviewController submitAnswer error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process interview response. Please retry.',
            ], 500);
        }
    }

    /**
     * Retrieve current session state and evaluation report.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $session = $request->user()->mockInterviewSessions()->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'session' => [
                'id' => $session->id,
                'role_title' => $session->role_title,
                'experience_level' => $session->experience_level,
                'tech_stack' => $session->tech_stack,
                'current_question_index' => $session->current_question_index,
                'total_questions' => $session->total_questions,
                'status' => $session->status,
                'conversation' => $session->conversation_history,
                'evaluation_report' => $session->evaluation_report,
                'created_at' => $session->created_at->toFormattedDateString(),
            ],
        ]);
    }

    /**
     * Get list of user's past mock interview sessions.
     */
    public function history(Request $request): JsonResponse
    {
        $sessions = $request->user()
            ->mockInterviewSessions()
            ->latest('id')
            ->take(15)
            ->get(['id', 'role_title', 'experience_level', 'status', 'current_question_index', 'total_questions', 'evaluation_report', 'created_at'])
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'role_title' => $s->role_title,
                    'experience_level' => $s->experience_level,
                    'status' => $s->status,
                    'overall_score' => $s->evaluation_report['overall_score'] ?? null,
                    'verdict' => $s->evaluation_report['verdict'] ?? null,
                    'created_at' => $s->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $sessions,
        ]);
    }
}
