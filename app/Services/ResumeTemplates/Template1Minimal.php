<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class Template1Minimal
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(['marginLeft' => 800, 'marginRight' => 800, 'marginTop' => 800, 'marginBottom' => 800]);

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 18, 'color' => '2c3e50', 'allCaps' => true], ['alignment' => Jc::CENTER]);
        if (!empty($data['job_title'])) {
            $section->addText($data['job_title'], ['size' => 12, 'color' => '555555'], ['alignment' => Jc::CENTER]);
        }
        $section->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

        $phpWord->addTableStyle('Base1Table', ['borderSize' => 1, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);

        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Certifications' => 'certifications', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $section->addText(strtoupper($title), ['bold' => true, 'size' => 11, 'color' => '2c3e50'], ['borderBottomSize' => 6, 'spaceAfter' => 60, 'spaceBefore' => 120]);
                if (in_array($key, ['summary', 'skills', 'certifications'])) {
                    $val = is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key];
                    $section->addText($val, [], ['spaceAfter' => 150]);
                } else {
                    foreach ($data[$key] as $item) {
                        $table = $section->addTable('Base1Table');
                        $table->addRow();
                        $table->addCell(6000)->addText($item['title'] ?? $item['degree'] ?? $item['institution'] ?? '', ['bold' => true]);
                        $table->addCell(4000)->addText($item['date'] ?? '', [], ['alignment' => Jc::RIGHT]);
                        
                        $sub = $item['company'] ?? '';
                        if (empty($sub) && !empty($item['degree'])) $sub = $item['institution'] ?? '';
                        if (!empty($item['location'])) $sub .= ' - ' . $item['location'];
                        if (!empty($item['gpa'])) $sub .= ' (GPA: ' . $item['gpa'] . ')';
                        if (!empty($item['link'])) $sub .= ' | ' . $item['link'];
                        
                        if (!empty($sub)) $section->addText($sub, ['italic' => true]);
                        
                        if (!empty($item['tech_stack'])) $section->addText($item['tech_stack'], ['size' => 9, 'color' => '555555']);
                        
                        if (!empty($item['description'])) {
                            $lines = explode("\n", $item['description']);
                            foreach ($lines as $line) {
                                $clean = trim(ltrim(trim($line), '-*• '));
                                if (!empty($clean)) $section->addListItem($clean, 0, null, \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED, ['spaceAfter' => 0]);
                            }
                        }
                        $section->addTextBreak(1, null, ['spaceAfter' => 100]);
                    }
                }
            }
        }
        
        return $this->save($phpWord, $format);
    }
    
    private function save($phpWord, $format) {
        if ($format === 'HTML') {
            $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $tempHtml = storage_path('app/' . uniqid('preview_') . '.html');
            $htmlWriter->save($tempHtml);
            $html = file_get_contents($tempHtml);
            unlink($tempHtml);
            return $html;
        }
        $tempFile = storage_path('app/' . uniqid('template_') . '.docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        return $tempFile;
    }
}