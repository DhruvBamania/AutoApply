<?php

namespace App\Http\Controllers;

use App\Models\MultilingualResume;
use App\Services\MultilingualResumeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MultilingualResumeController extends Controller
{
    protected MultilingualResumeService $service;

    public function __construct(MultilingualResumeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the Multilingual Resumes Hub on the candidate side.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $hasBaseResume = !empty($user->resume_text) || !empty($user->resume_json);

        $resumes = $user->multilingualResumes()->get();
        $presets = MultilingualResumeService::getPresets();

        $templates = [
            1 => 'Classic Minimal',
            2 => 'Modern Professional',
            3 => 'Executive Blue',
            4 => 'Creative Focus',
            5 => 'Tech Focus',
            6 => 'Classic Serif',
            7 => 'Elegant Purple',
            8 => 'Clean Teal',
            9 => 'Bold Red',
            10 => 'Startup Orange',
            11 => 'Corporate Blue',
            12 => 'Academic Palatino',
            13 => 'Modern Dark',
            14 => 'Simple Green',
            15 => 'Dynamic Orange',
        ];

        return view('resumes.multilingual', compact('resumes', 'presets', 'templates', 'hasBaseResume'));
    }

    /**
     * Generate translated resume(s) in one or more languages.
     */
    public function generate(Request $request)
    {
        $user = $request->user();

        if (empty($user->resume_text) && empty($user->resume_json)) {
            return redirect()->route('dashboard')
                ->with('error', 'Please upload or create your base resume first before translating into other languages.');
        }

        $validated = $request->validate([
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'language' => 'nullable|string|max:50',
            'custom_language' => 'nullable|string|max:50',
            'template_id' => 'nullable|integer|min:1|max:15',
            'target_role' => 'nullable|string|max:150',
        ]);

        $languagesToProcess = [];

        // Check if multiple checkboxes were selected
        if (!empty($validated['languages'])) {
            $languagesToProcess = array_merge($languagesToProcess, $validated['languages']);
        }

        // Check if single language dropdown/radio was passed
        if (!empty($validated['language'])) {
            $languagesToProcess[] = $validated['language'];
        }

        // Check custom language text input
        if (!empty($validated['custom_language'])) {
            $customLangs = explode(',', $validated['custom_language']);
            foreach ($customLangs as $cl) {
                $cl = trim($cl);
                if (!empty($cl)) {
                    $languagesToProcess[] = $cl;
                }
            }
        }

        $languagesToProcess = array_unique(array_filter(array_map('trim', $languagesToProcess)));

        if (empty($languagesToProcess)) {
            return redirect()->back()
                ->with('error', 'Please select at least one language preset or enter a custom language.');
        }

        $templateId = (int) ($validated['template_id'] ?? 1);
        $targetRole = $validated['target_role'] ?? null;

        try {
            $created = $this->service->batchTranslate($user, $languagesToProcess, $templateId, $targetRole);

            $count = count($created);
            $langNames = implode(', ', array_map(fn($r) => $r->language_flag . ' ' . $r->language_name, $created));

            return redirect()->route('multilingual_resumes.index')
                ->with('success', "✨ Generated {$count} multilingual resume(s) successfully: {$langNames}!");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Translation error: ' . $e->getMessage());
        }
    }

    /**
     * Preview translated resume in selected ATS template.
     */
    public function preview(Request $request, int $id)
    {
        $resume = $request->user()->multilingualResumes()->findOrFail($id);

        try {
            $html = $this->service->generatePreviewHtml($resume);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'html' => $html,
                    'resume' => [
                        'id' => $resume->id,
                        'language_name' => $resume->language_name,
                        'language_flag' => $resume->language_flag,
                        'target_role' => $resume->target_role,
                        'template_id' => $resume->template_id,
                        'created_at' => $resume->created_at->toFormattedDateString(),
                    ],
                ]);
            }

            return view('resumes.multilingual_preview', compact('resume', 'html'));
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to generate preview: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Unable to preview resume: ' . $e->getMessage());
        }
    }

    /**
     * Download translated resume in Microsoft Word (.docx) ATS format.
     */
    public function download(Request $request, int $id): BinaryFileResponse
    {
        $resume = $request->user()->multilingualResumes()->findOrFail($id);

        $tempFile = $this->service->generateDocx($resume);

        $userName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $resume->user->name ?? 'Candidate'));
        $langClean = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $resume->language_name));
        $filename = "{$userName}_{$langClean}_Resume.docx";

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Delete a saved translated resume.
     */
    public function destroy(Request $request, int $id)
    {
        $resume = $request->user()->multilingualResumes()->findOrFail($id);
        $langName = $resume->language_name;
        $resume->delete();

        return redirect()->route('multilingual_resumes.index')
            ->with('success', "Deleted {$langName} resume version.");
    }
}

