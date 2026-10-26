<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class Template10Startup
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Trebuchet MS');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 24, 'color' => 'f39c12'], ['alignment' => Jc::RIGHT]);
        if (!empty($data['job_title'])) $section->addText($data['job_title'], ['size' => 12], ['alignment' => Jc::RIGHT]);
        $section->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::RIGHT, 'spaceAfter' => 300, 'borderBottomSize' => 18, 'borderBottomColor' => 'f39c12']);

        $phpWord->addTableStyle('TimelineTable', ['borderSize' => 0, 'cellMargin' => 50]);
        
        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $table = $section->addTable('TimelineTable');
                $table->addRow();
                $left = $table->addCell(2500, ['valign' => 'top']);
                $left->addText(strtoupper($title), ['bold' => true, 'color' => 'f39c12', 'size' => 11], ['alignment' => Jc::RIGHT]);
                
                $right = $table->addCell(7500, ['valign' => 'top', 'borderLeftSize' => 6, 'borderLeftColor' => 'f39c12']);
                
                if (in_array($key, ['summary', 'skills'])) {
                    $val = is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key];
                    $right->addText($val, [], ['spaceAfter' => 150]);
                } else {
                    foreach ($data[$key] as $item) {
                        $right->addText($item['title'] ?? $item['degree'] ?? $item['institution'] ?? '', ['bold' => true]);
                        $sub = $item['company'] ?? $item['institution'] ?? '';
                        if (!empty($item['date'])) $sub .= ' | ' . $item['date'];
                        $right->addText($sub, ['italic' => true, 'color' => '555555']);
                        
                        if (!empty($item['description'])) {
                            $lines = explode("\n", $item['description']);
                            foreach ($lines as $line) {
                                $clean = trim(ltrim(trim($line), '-*• '));
                                if (!empty($clean)) $right->addListItem($clean, 0, null, \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED, ['spaceAfter' => 0]);
                            }
                        }
                        $right->addTextBreak(1, null, ['spaceAfter' => 100]);
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