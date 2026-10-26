<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class Template9Bold
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Helvetica');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 22, 'color' => 'c0392b']);
        if (!empty($data['job_title'])) $section->addText($data['job_title'], ['size' => 14, 'color' => '333333']);
        $section->addText($data['contact'] ?? '', ['size' => 9], ['spaceAfter' => 200]);

        $phpWord->addTableStyle('HeaderTable', ['borderSize' => 0, 'cellMargin' => 50, 'bgColor' => 'c0392b']);
        
        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $table = $section->addTable('HeaderTable');
                $table->addRow();
                $table->addCell(10000)->addText(strtoupper($title), ['bold' => true, 'color' => 'FFFFFF', 'size' => 12]);
                
                $section->addTextBreak(1, null, ['spaceAfter' => 60]);

                if (in_array($key, ['summary', 'skills'])) {
                    $val = is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key];
                    $section->addText($val, [], ['spaceAfter' => 150]);
                } else {
                    foreach ($data[$key] as $item) {
                        $section->addText($item['title'] ?? $item['degree'] ?? $item['institution'] ?? '', ['bold' => true]);
                        $sub = $item['company'] ?? $item['institution'] ?? '';
                        if (!empty($item['date'])) $sub .= ' | ' . $item['date'];
                        $section->addText($sub, ['italic' => true, 'color' => '555555']);
                        
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