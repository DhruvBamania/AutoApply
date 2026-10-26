<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResumeTemplates\Template1Minimal;
use App\Services\ResumeTemplates\Template2Modern;
use App\Services\ResumeTemplates\Template3Executive;
use App\Services\ResumeTemplates\Template4Creative;
use App\Services\ResumeTemplates\Template5Tech;
use App\Services\ResumeTemplates\Template6Classic;
use App\Services\ResumeTemplates\Template7Elegant;
use App\Services\ResumeTemplates\Template8Clean;
use App\Services\ResumeTemplates\Template9Bold;
use App\Services\ResumeTemplates\Template10Startup;
use App\Services\ResumeTemplates\Template11Corporate;
use App\Services\ResumeTemplates\Template12Academic;
use App\Services\ResumeTemplates\Template13Modern;
use App\Services\ResumeTemplates\Template14Simple;
use App\Services\ResumeTemplates\Template15Dynamic;

class ResumeEditorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if (session()->has('role_resume_data')) {
            $resumeData = session()->get('role_resume_data');
        } else {
            $resumeData = $user->resume_json ? json_decode($user->resume_json, true) : [];
        }
        
        $templates = [
            1 => 'Classic Minimal', 2 => 'Modern Professional', 3 => 'Executive Blue',
            4 => 'Creative Focus', 5 => 'Tech Focus', 6 => 'Classic Serif',
            7 => 'Elegant Purple', 8 => 'Clean Teal', 9 => 'Bold Red',
            10 => 'Startup Orange', 11 => 'Corporate Blue', 12 => 'Academic Palatino',
            13 => 'Modern Dark', 14 => 'Simple Green', 15 => 'Dynamic Orange'
        ];

        $selectedTemplate = request()->query('template', 1);

        return view('resume_editor', compact('resumeData', 'templates', 'selectedTemplate'));
    }

    public function preview(Request $request)
    {
        $data = $this->sanitizeData($request->all());
        $templateId = (int) $request->input('template_id', 1);
        
        $templateService = $this->getTemplateService($templateId);
        if (!$templateService) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $htmlContent = $templateService->generate($data, 'HTML');
        
        // Extract body
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
            $htmlContent = $matches[1];
        }

        return response()->json(['html' => $htmlContent]);
    }

    public function download(Request $request)
    {
        $data = $this->sanitizeData($request->all());
        $templateId = (int) $request->input('template_id', 1);
        
        $templateService = $this->getTemplateService($templateId);
        if (!$templateService) {
            return redirect()->back()->with('error', 'Template not found.');
        }

        $tempFile = $templateService->generate($data, 'Word2007');

        $nameParts = explode(' ', trim($data['name'] ?? 'User'));
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[count($nameParts)-1] ?? '';
        if (count($nameParts) === 1) $lastName = '';
        
        $filename = $firstName . ($lastName ? '_' . $lastName : '') . '_template' . $templateId . '_resume.docx';

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function sanitizeData(array $input): array
    {
        if (!empty($input['skills']) && is_string($input['skills'])) {
            $input['skills'] = array_map('trim', explode(',', $input['skills']));
        }
        if (!empty($input['certifications']) && is_string($input['certifications'])) {
            $input['certifications'] = array_map('trim', explode(',', $input['certifications']));
        }

        foreach (['experience', 'projects', 'education'] as $key) {
            if (isset($input[$key]) && is_array($input[$key])) {
                $input[$key] = array_values(array_filter($input[$key], function($item) {
                    return !empty(array_filter($item));
                }));
            }
        }
        
        return $input;
    }

    private function getTemplateService($id)
    {
        switch ($id) {
            case 1:  return new Template1Minimal();
            case 2:  return new Template2Modern();
            case 3:  return new Template3Executive();
            case 4:  return new Template4Creative();
            case 5:  return new Template5Tech();
            case 6:  return new Template6Classic();
            case 7:  return new Template7Elegant();
            case 8:  return new Template8Clean();
            case 9:  return new Template9Bold();
            case 10: return new Template10Startup();
            case 11: return new Template11Corporate();
            case 12: return new Template12Academic();
            case 13: return new Template13Modern();
            case 14: return new Template14Simple();
            case 15: return new Template15Dynamic();
            default: return null;
        }
    }
}
