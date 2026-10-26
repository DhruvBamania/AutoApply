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

class TemplateController extends Controller
{
    public function index()
    {
        $templates = [
            [
                'id' => 1,
                'name' => 'Classic Minimal',
                'description' => 'A clean, ATS-friendly layout with no distractions. Perfect for traditional corporate roles.',
                'color' => '#f8f9fa',
                'preview' => 'preview_1.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Modern Professional',
                'description' => 'A sharp, modern aesthetic with distinct section headers. Great for tech and startup roles.',
                'color' => '#e2e8f0',
                'preview' => 'preview_2.jpg'
            ],
            [
                'id' => 3,
                'name' => 'Executive Blue',
                'description' => 'A bold, commanding layout with navy blue accents. Tailored for management and leadership.',
                'color' => '#1e3a8a',
                'preview' => 'preview_3.jpg'
            ],
            [
                'id' => 4,
                'name' => 'Creative Focus',
                'description' => 'A slightly playful, vibrant layout to stand out. Ideal for marketing and design roles.',
                'color' => '#8b5cf6',
                'preview' => 'preview_4.jpg'
            ],
            [
                'id' => 5,
                'name' => 'Tech Focus',
                'description' => 'Clean Verdana font style geared towards software engineers and IT professionals.',
                'color' => '#2c3e50',
                'preview' => 'preview_1.jpg'
            ],
            [
                'id' => 6,
                'name' => 'Classic Serif',
                'description' => 'A timeless Times New Roman template for a highly professional, academic feel.',
                'color' => '#000000',
                'preview' => 'preview_2.jpg'
            ],
            [
                'id' => 7,
                'name' => 'Elegant Purple',
                'description' => 'Sophisticated Garamond layout with subtle purple accents for creative fields.',
                'color' => '#8e44ad',
                'preview' => 'preview_3.jpg'
            ],
            [
                'id' => 8,
                'name' => 'Clean Teal',
                'description' => 'Fresh and clean Tahoma styling with teal headings for a balanced presentation.',
                'color' => '#16a085',
                'preview' => 'preview_4.jpg'
            ],
            [
                'id' => 9,
                'name' => 'Bold Red',
                'description' => 'Stand out instantly with strong Arial fonts and striking red structural borders.',
                'color' => '#c0392b',
                'preview' => 'preview_1.jpg'
            ],
            [
                'id' => 10,
                'name' => 'Startup Orange',
                'description' => 'Modern Helvetica layout with energetic orange accents perfect for startup culture.',
                'color' => '#f39c12',
                'preview' => 'preview_2.jpg'
            ],
            [
                'id' => 11,
                'name' => 'Corporate Blue',
                'description' => 'Standardized corporate structure using classic Calibri and soft blue elements.',
                'color' => '#2980b9',
                'preview' => 'preview_3.jpg'
            ],
            [
                'id' => 12,
                'name' => 'Academic Palatino',
                'description' => 'Refined academic formatting using Palatino fonts for research or university roles.',
                'color' => '#000000',
                'preview' => 'preview_4.jpg'
            ],
            [
                'id' => 13,
                'name' => 'Modern Dark',
                'description' => 'Sleek Trebuchet MS styling featuring dark grey aesthetic structures.',
                'color' => '#34495e',
                'preview' => 'preview_1.jpg'
            ],
            [
                'id' => 14,
                'name' => 'Simple Green',
                'description' => 'Minimalist Arial design focusing on readability with refreshing green accents.',
                'color' => '#27ae60',
                'preview' => 'preview_2.jpg'
            ],
            [
                'id' => 15,
                'name' => 'Dynamic Orange',
                'description' => 'High-energy Verdana template with sharp orange structure for impact.',
                'color' => '#d35400',
                'preview' => 'preview_3.jpg'
            ]
        ];

        return view('templates.index', compact('templates'));
    }

    public function preview($id)
    {
        $user = auth()->user();
        if (!$user->resume_json) {
            return redirect()->back()->with('error', 'You must upload a resume first to preview templates.');
        }

        $data = json_decode($user->resume_json, true);
        $templateService = $this->getTemplateService($id);
        
        if (!$templateService) {
            return redirect()->back()->with('error', 'Template not found.');
        }

        $htmlContent = $templateService->generate($data, 'HTML');

        // Extract just the body content to avoid injecting full html/head tags into our view
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
            $htmlContent = $matches[1];
        }

        return view('templates.preview', compact('id', 'htmlContent'));
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

    public function download($id)
    {
        $user = auth()->user();
        if (!$user->resume_json) {
            return redirect()->back()->with('error', 'You must upload a resume first to generate templates.');
        }

        $data = json_decode($user->resume_json, true);

        $templateService = $this->getTemplateService($id);
        if (!$templateService) {
            return redirect()->back()->with('error', 'Template not found.');
        }
        
        $templateShortName = 'template' . $id;

        $tempFile = $templateService->generate($data, 'Word2007');

        // Build the download filename
        $nameParts = explode(' ', trim($data['name'] ?? 'User'));
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[count($nameParts)-1] ?? '';
        if (count($nameParts) === 1) $lastName = '';
        
        $filenameName = $firstName . ($lastName ? '_' . $lastName : '');
        $finalFilename = $filenameName . '_' . $templateShortName . '_resume.docx';

        return response()->download($tempFile, $finalFilename)->deleteFileAfterSend(true);
    }
}
