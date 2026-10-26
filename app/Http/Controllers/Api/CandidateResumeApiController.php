<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailoredResume;
use App\Services\ResumeParserService;
use App\Services\ResumeTailoringService;
use App\Services\GroqAiService;
use Illuminate\Http\Request;

class CandidateResumeApiController extends Controller
{
    protected ResumeParserService $parser;
    protected ResumeTailoringService $tailor;

    public function __construct(ResumeParserService $parser, ResumeTailoringService $tailor)
    {
        $this->parser = $parser;
        $this->tailor = $tailor;
    }

    /**
     * Get candidate's parsed resume and JSON structure.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'resume_text' => $user->resume_text,
            'resume_json' => $user->resume_json ? json_decode($user->resume_json, true) : null,
            'tailored_resumes' => $user->tailoredResumes()->latest()->take(10)->get(),
        ]);
    }

    /**
     * Upload resume PDF/DOCX and parse into structured JSON.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:10240',
        ]);

        $user = $request->user();
        $text = $this->parser->parseAndSave($request->file('resume'), $user);

        return response()->json([
            'success' => true,
            'message' => 'Resume uploaded and parsed successfully.',
            'resume_text' => $text,
            'resume_json' => json_decode($user->fresh()->resume_json, true),
        ]);
    }

    /**
     * Tailor resume for a target company and job description using AI.
     */
    public function tailor(Request $request, GroqAiService $groq)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'job_description' => 'required|string',
        ]);

        $user = $request->user();

        if (empty($user->resume_text)) {
            return response()->json(['error' => 'Please upload or provide a base resume first.'], 400);
        }

        $companyName = $request->company_name ?? 'the target company';
        $jobDescription = $request->job_description;

        $prompt = "You are an expert ATS resume writer. I want you to tailor the following resume specifically for the job description provided. 
Inject highly relevant, ATS-friendly keywords specific to this role throughout the summary, experience bullets, and skills section.
Target Company: {$companyName}
Target Job Description: {$jobDescription}
Retain the truthful base facts (dates, companies, schools) but rewrite the descriptions to heavily emphasize skills and achievements relevant to this job.
Return ONLY valid JSON that matches this EXACT structure, with no markdown formatting or extra text:
{
  \"name\": \"\",
  \"job_title\": \"\",
  \"contact\": \"\",
  \"summary\": \"\",
  \"skills\": [\"skill1\", \"skill2\"],
  \"experience\": [{\"title\": \"\", \"company\": \"\", \"location\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"projects\": [{\"title\": \"\", \"link\": \"\", \"tech_stack\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"location\": \"\", \"gpa\": \"\", \"date\": \"\"}],
  \"certifications\": [\"cert1\", \"cert2\"]
}

Base Resume Text:
" . $user->resume_text;

        $responseStr = $groq->generate($prompt);
        if (!$responseStr) {
            return response()->json(['error' => 'AI tailoring failed. Please try again.'], 500);
        }

        $jsonStr = trim(str_replace(['```json', '```'], '', $responseStr));
        $resumeData = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'AI returned invalid JSON response.'], 500);
        }

        $saved = TailoredResume::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'job_description' => $request->job_description,
            'tailored_content' => $jsonStr,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Resume tailored successfully for ' . $companyName,
            'tailored_id' => $saved->id,
            'tailored_resume' => $resumeData,
        ]);
    }

    /**
     * List all 15 ATS resume templates with preview metadata.
     */
    public function templates()
    {
        $templates = [
            ['id' => 1, 'name' => 'Minimalist Clean', 'style' => 'Minimal', 'description' => 'Single column ultra-clean ATS friendly format.'],
            ['id' => 2, 'name' => 'Modern Professional', 'style' => 'Modern', 'description' => 'Modern typography with clear category borders.'],
            ['id' => 3, 'name' => 'Executive Suite', 'style' => 'Executive', 'description' => 'Sophisticated layout for senior leadership & management.'],
            ['id' => 4, 'name' => 'Creative Tech', 'style' => 'Creative', 'description' => 'Accented header styling for software engineers and designers.'],
            ['id' => 5, 'name' => 'Tech Specialist', 'style' => 'Tech', 'description' => 'Emphasizes tech stack, repositories, and technical skills.'],
            ['id' => 6, 'name' => 'Classic Traditional', 'style' => 'Classic', 'description' => 'Timeless serif font hierarchy accepted across all industries.'],
            ['id' => 7, 'name' => 'Elegant Corporate', 'style' => 'Elegant', 'description' => 'Refined spacing tailored for corporate roles.'],
            ['id' => 8, 'name' => 'Clean Slate', 'style' => 'Clean', 'description' => 'High scannability and compact ATS structure.'],
            ['id' => 9, 'name' => 'Bold Impact', 'style' => 'Bold', 'description' => 'Bold section headers designed to capture recruiter attention.'],
            ['id' => 10, 'name' => 'Startup Dynamo', 'style' => 'Startup', 'description' => 'Fast-paced, product-oriented layout for high-growth tech.'],
            ['id' => 11, 'name' => 'Corporate Leader', 'style' => 'Corporate', 'description' => 'Balanced corporate design highlighting career progression.'],
            ['id' => 12, 'name' => 'Academic & Research', 'style' => 'Academic', 'description' => 'Detailed sections for publications, projects, and education.'],
            ['id' => 13, 'name' => 'Modern Grid', 'style' => 'Modern', 'description' => 'Compact modular grid for cross-functional skills.'],
            ['id' => 14, 'name' => 'Simple Essential', 'style' => 'Simple', 'description' => 'Distraction-free structure with 100% parser pass rate.'],
            ['id' => 15, 'name' => 'Dynamic Frontier', 'style' => 'Dynamic', 'description' => 'Vibrant layout tailored for cutting-edge AI and tech talent.'],
        ];

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Export custom or tailored resume to DOCX download URL.
     */
    public function export(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer|min:1|max:15',
            'resume_data' => 'nullable|array',
        ]);

        $user = $request->user();
        $templateId = (int)$request->template_id;

        $resumeData = $request->input('resume_data');
        if (!$resumeData && $user->resume_json) {
            $resumeData = json_decode($user->resume_json, true);
        }

        if (!$resumeData) {
            return response()->json(['error' => 'No resume data provided or found.'], 400);
        }

        $classMap = [
            1 => 'Template1Minimal', 2 => 'Template2Modern', 3 => 'Template3Executive',
            4 => 'Template4Creative', 5 => 'Template5Tech', 6 => 'Template6Classic',
            7 => 'Template7Elegant', 8 => 'Template8Clean', 9 => 'Template9Bold',
            10 => 'Template10Startup', 11 => 'Template11Corporate', 12 => 'Template12Academic',
            13 => 'Template13Modern', 14 => 'Template14Simple', 15 => 'Template15Dynamic'
        ];

        $fullClassName = "App\\Services\\ResumeTemplates\\" . $classMap[$templateId];
        if (!class_exists($fullClassName)) {
            return response()->json(['error' => 'Template class not found.'], 404);
        }

        $templateService = new $fullClassName();
        $tempFile = $templateService->generate($resumeData, 'Word2007');

        $downloadsDir = public_path('downloads');
        if (!file_exists($downloadsDir)) {
            mkdir($downloadsDir, 0777, true);
        }

        $templateShortName = 'template' . $templateId;
        $nameParts = explode(' ', trim($resumeData['name'] ?? 'User'));
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[count($nameParts) - 1] ?? '';
        if (count($nameParts) === 1) $lastName = '';

        $finalFilename = $firstName . ($lastName ? '_' . $lastName : '') . '_template_' . $templateShortName . '_' . uniqid() . '.docx';
        $publicPath = $downloadsDir . DIRECTORY_SEPARATOR . $finalFilename;
        copy($tempFile, $publicPath);
        @unlink($tempFile);

        return response()->json([
            'success' => true,
            'message' => 'Resume exported to DOCX successfully.',
            'template_id' => $templateId,
            'download_url' => url('downloads/' . $finalFilename),
        ]);
    }
}
