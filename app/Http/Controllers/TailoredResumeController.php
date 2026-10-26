<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResumeTailoringService;
use App\Models\TailoredResume;

class TailoredResumeController extends Controller
{
    public function index()
    {
        $resumes = auth()->user()->tailoredResumes()->latest()->get();
        return view('resumes.tailor', compact('resumes'));
    }

    public function generate(Request $request, ResumeTailoringService $service)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'job_description' => 'required|string',
        ]);

        if (!auth()->user()->resume_text) {
            return back()->with('error', 'Please upload a base resume on the Dashboard first.');
        }

        try {
            $service->generate(auth()->user(), $request->company_name, $request->job_description);
            return back()->with('success', 'Resume successfully tailored for ' . ($request->company_name ?? 'the job') . '!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to tailor resume: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $resume = auth()->user()->tailoredResumes()->findOrFail($id);
        return view('resumes.show', compact('resume'));
    }

    public function destroy($id)
    {
        $resume = auth()->user()->tailoredResumes()->findOrFail($id);
        $resume->delete();
        return back()->with('success', 'Tailored resume deleted.');
    }

    public function download($id)
    {
        $resume = auth()->user()->tailoredResumes()->findOrFail($id);
        
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Define global professional font
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);
        
        // Professional ATS-friendly margins (narrow)
        $section = $phpWord->addSection([
            'marginLeft'   => 700,
            'marginRight'  => 700,
            'marginTop'    => 700,
            'marginBottom' => 700,
        ]);

        // Define Professional Resume Styles (Navy Blue Theme)
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 20, 'color' => '1f3864'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 12, 'color' => '1f3864', 'allCaps' => true], ['borderBottomSize' => 6, 'borderBottomColor' => '1f3864', 'spaceBefore' => 200, 'spaceAfter' => 100]);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 11, 'color' => '000000'], ['spaceBefore' => 120, 'spaceAfter' => 60]);
        
        $textRunStyle = ['spaceAfter' => 0];
        
        // Invisible table style for left/right alignment
        $phpWord->addTableStyle('AlignmentTable', ['borderSize' => 0, 'cellMargin' => 0]);

        $lines = explode("\n", $resume->tailored_content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (str_starts_with($line, '# ')) {
                $section->addTitle(substr($line, 2), 1);
            } elseif (str_starts_with($line, '## ')) {
                $section->addTitle(substr($line, 3), 2);
            } elseif (str_starts_with($line, '### ')) {
                $section->addTitle(substr($line, 4), 3);
            } elseif (str_starts_with($line, 'JOBTITLE||') || str_starts_with($line, 'EDUCATIONTITLE||')) {
                // Format: JOBTITLE||Title - Company||Date
                $parts = explode('||', $line);
                if (count($parts) >= 3) {
                    $titleAndCompany = str_replace(['**', '*'], '', $parts[1]);
                    $date = str_replace(['**', '*'], '', $parts[2]);
                    
                    $table = $section->addTable('AlignmentTable');
                    $table->addRow();
                    $table->addCell(7000)->addText($titleAndCompany, ['bold' => true], ['spaceAfter' => 0]);
                    $table->addCell(3000)->addText($date, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT, 'spaceAfter' => 0]);
                }
            } elseif (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
                $cleanLine = str_replace(['**', '*'], '', substr($line, 2));
                $section->addListItem($cleanLine, 0, null, \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED, $textRunStyle);
            } else {
                $cleanLine = str_replace(['**', '*'], '', $line);
                
                // If it looks like subtitle contact info with a |, center it
                if (str_contains($cleanLine, '|') && !str_starts_with($line, 'JOBTITLE')) {
                    $section->addText($cleanLine, ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
                } else {
                    $section->addText($cleanLine, [], $textRunStyle);
                }
            }
        }
        
        $fileName = 'Resume_' . str_replace(' ', '_', $resume->company_name ?? 'Tailored') . '.docx';
        $tempFile = storage_path('app/' . uniqid('resume_') . '.docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
