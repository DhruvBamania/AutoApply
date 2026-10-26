<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GroqAiService;
use Exception;

class RoleResumeController extends Controller
{
    protected $groqService;

    public function __construct(GroqAiService $groqService)
    {
        $this->groqService = $groqService;
    }

    public function index()
    {
        return view('resumes.role_resume');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'job_role' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $baseResumeData = $user->resume_text;

        if (!$baseResumeData) {
            return redirect()->back()->with('error', 'Please upload your resume in the Dashboard first.');
        }

        $jobRole = $request->input('job_role');

        $prompt = "You are an expert ATS resume writer. I want you to tailor the following resume specifically for the job role of '{$jobRole}'. 
Inject highly relevant, ATS-friendly keywords specific to this role throughout the summary, experience bullets, and skills section.
Retain the truthful base facts (dates, companies, schools) but rewrite the descriptions to heavily emphasize skills and achievements relevant to a {$jobRole}.
Return ONLY valid JSON that matches this EXACT structure, with no markdown formatting or extra text:
{
  \"name\": \"\",
  \"job_title\": \"{$jobRole}\",
  \"contact\": \"\",
  \"summary\": \"\",
  \"skills\": [\"skill1\", \"skill2\"],
  \"experience\": [{\"title\": \"\", \"company\": \"\", \"location\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"projects\": [{\"title\": \"\", \"link\": \"\", \"tech_stack\": \"\", \"date\": \"\", \"description\": \"\"}],
  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"location\": \"\", \"gpa\": \"\", \"date\": \"\"}],
  \"certifications\": [\"cert1\", \"cert2\"]
}

Base Resume Text:
" . $baseResumeData;

        try {
            $response = $this->groqService->generate($prompt);
            
            if (!$response) {
                throw new Exception("AI returned empty response.");
            }

            // Clean markdown if present
            $jsonStr = str_replace(['```json', '```'], '', $response);
            $jsonStr = trim($jsonStr);
            
            // Validate JSON
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
            }

            // Store in session so Dynamic Editor can pick it up
            session()->put('role_resume_data', $decoded);
            session()->put('role_resume_target', $jobRole);

            return redirect()->route('resume_editor.index')->with('success', "Role-specific resume for {$jobRole} generated! You can now edit it and download.");

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate role resume: ' . $e->getMessage());
        }
    }
}
