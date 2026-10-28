<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    protected AiChatService $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Retrieve initial context and role-based suggestion chips.
     */
    public function context(Request $request): JsonResponse
    {
        $user = auth()->user();
        $context = $this->aiChatService->getInitialContext($user);

        return response()->json([
            'status' => 'success',
            'data' => $context,
        ]);
    }

    /**
     * Process an incoming chat message and return an AI response.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:2000',
            'history' => 'nullable|array|max:16',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:3000',
        ]);

        try {
            $user = auth()->user();
            $result = $this->aiChatService->generateReply(
                $validated['message'],
                $validated['history'] ?? [],
                $user
            );

            return response()->json([
                'status' => 'success',
                'reply' => $result['reply'],
                'jobs' => $result['jobs'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('AiChatController Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while generating the AI response. Please try again.',
            ], 500);
        }
    }
}

