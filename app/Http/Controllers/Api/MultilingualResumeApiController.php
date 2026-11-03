<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MultilingualResume;
use App\Services\MultilingualResumeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultilingualResumeApiController extends Controller
{
    protected MultilingualResumeService $service;

    public function __construct(MultilingualResumeService $service)
    {
        $this->service = $service;
    }

    /**
     * Get list of candidate's translated resumes and supported language presets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $resumes = $user->multilingualResumes()->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'language_name' => $r->language_name,
                'language_code' => $r->language_code,
                'language_flag' => $r->language_flag,
                'template_id' => $r->template_id,
                'target_role' => $r->target_role,
                'skills_count' => count($r->resume_data['skills'] ?? []),
                'experience_count' => count($r->resume_data['experience'] ?? []),
                'created_at' => $r->created_at->toFormattedDateString(),
            ];
        });

        return response()->json([
            'success' => true,
            'has_base_resume' => !empty($user->resume_text) || !empty($user->resume_json),
            'resumes' => $resumes,
            'presets' => MultilingualResumeService::getPresets(),
        ]);
    }

    /**
     * View single translated resume with structured JSON data.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $resume = $request->user()->multilingualResumes()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $resume->id,
                'language_name' => $resume->language_name,
                'language_code' => $resume->language_code,
                'language_flag' => $resume->language_flag,
                'template_id' => $resume->template_id,
                'target_role' => $resume->target_role,
                'resume_data' => $resume->resume_data,
                'created_at' => $resume->created_at->toFormattedDateString(),
            ],
        ]);
    }

    /**
     * Translate candidate resume into one or more languages.
     */
    public function translate(Request $request): JsonResponse
    {
        $user = $request->user();

        if (empty($user->resume_text) && empty($user->resume_json)) {
            return response()->json([
                'success' => false,
                'error' => 'Please upload or provide a base resume first before translating.',
            ], 400);
        }

        $validated = $request->validate([
            'language' => 'nullable|string|max:50',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'template_id' => 'nullable|integer|min:1|max:15',
            'target_role' => 'nullable|string|max:150',
        ]);

        $langs = [];
        if (!empty($validated['languages'])) {
            $langs = array_merge($langs, $validated['languages']);
        }
        if (!empty($validated['language'])) {
            $langs[] = $validated['language'];
        }

        $langs = array_unique(array_filter(array_map('trim', $langs)));

        if (empty($langs)) {
            return response()->json([
                'success' => false,
                'error' => 'Please provide at least one target language.',
            ], 422);
        }

        $templateId = (int) ($validated['template_id'] ?? 1);
        $targetRole = $validated['target_role'] ?? null;

        try {
            $created = $this->service->batchTranslate($user, $langs, $templateId, $targetRole);

            return response()->json([
                'success' => true,
                'message' => 'Successfully translated resume into ' . count($created) . ' language(s).',
                'resumes' => $created,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a translated resume.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $resume = $request->user()->multilingualResumes()->findOrFail($id);
        $resume->delete();

        return response()->json([
            'success' => true,
            'message' => 'Translated resume deleted successfully.',
        ]);
    }
}

