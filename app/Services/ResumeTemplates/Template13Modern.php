<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class Template13Modern
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Verdana');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $phpWord->addTableStyle('CardHeader', ['borderSize' => 18, 'borderColor' => 'e67e22', 'cellMargin' => 100, 'bgColor' => 'f8f9fa']);
        $table = $section->addTable('CardHeader');
        $table->addRow();
        $cell = $table->addCell(10000);
        $cell->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 20, 'color' => 'e67e22'], ['alignment' => Jc::CENTER]);
        if (!empty($data['job_title'])) $cell->addText($data['job_title'], ['size' => 12], ['alignment' => Jc::CENTER]);
        $cell->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::CENTER]);

        $section->addTextBreak(1);
        $phpWord->addTableStyle('CardBody', ['borderSize' => 6, 'borderColor' => 'e67e22', 'cellMargin' => 100]);

        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $section->addText(strtoupper($title), ['bold' => true, 'color' => 'e67e22', 'size' => 12], ['spaceAfter' => 60]);
                $table = $section->addTable('CardBody');
                $table->addRow();
                $cell = $table->addCell(10000);
                
                if (in_array($key, ['summary', 'skills'])) {
                    $val = is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key];
                    $cell->addText($val, [], ['spaceAfter' => 0]);
                } else {
                    foreach ($data[$key] as $item) {
                        $cell->addText($item['title'] ?? $item['degree'] ?? $item['institution'] ?? '', ['bold' => true]);
                        $sub = $item['company'] ?? $item['institution'] ?? '';
                        if (!empty($item['date'])) $sub .= ' | ' . $item['date'];
                        $cell->addText($sub, ['italic' => true, 'color' => '555555']);
                        
                        if (!empty($item['description'])) {
                            $lines = explode("\n", $item['description']);
                            foreach ($lines as $line) {
                                $clean = trim(ltrim(trim($line), '-*• '));
                                if (!empty($clean)) $cell->addListItem($clean, 0, null, \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED, ['spaceAfter' => 0]);
                            }
                        }
                        $cell->addTextBreak(1, null, ['spaceAfter' => 60]);
                    }
                }
                $section->addTextBreak(1);
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