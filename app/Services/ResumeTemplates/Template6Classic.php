<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class Template6Classic
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(9);
        $section = $phpWord->addSection(['marginLeft' => 600, 'marginRight' => 600, 'marginTop' => 600, 'marginBottom' => 600]);

        $phpWord->addTableStyle('LayoutTable', ['borderSize' => 0, 'cellMargin' => 50]);
        $table = $section->addTable('LayoutTable');
        $table->addRow();

        // Left Column
        $left = $table->addCell(3000, ['bgColor' => 'f4f4f4']);
        $left->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 16, 'color' => '000000']);
        if (!empty($data['job_title'])) $left->addText($data['job_title'], ['bold' => true, 'size' => 10]);
        $left->addTextBreak(1);
        
        $left->addText('CONTACT', ['bold' => true, 'color' => '000000'], ['borderBottomSize' => 6]);
        $contacts = explode('|', $data['contact'] ?? '');
        foreach ($contacts as $c) {
            $left->addText(trim($c), ['size' => 8], ['spaceAfter' => 0]);
        }
        $left->addTextBreak(1);

        if (!empty($data['skills'])) {
            $left->addText('SKILLS', ['bold' => true, 'color' => '000000'], ['borderBottomSize' => 6]);
            $skills = is_array($data['skills']) ? $data['skills'] : explode(',', $data['skills']);
            foreach ($skills as $skill) {
                $left->addText(trim($skill), ['size' => 9], ['spaceAfter' => 0]);
            }
            $left->addTextBreak(1);
        }
        if (!empty($data['certifications'])) {
            $left->addText('CERTIFICATIONS', ['bold' => true, 'color' => '000000'], ['borderBottomSize' => 6]);
            $certs = is_array($data['certifications']) ? $data['certifications'] : explode(',', $data['certifications']);
            foreach ($certs as $cert) {
                $left->addText(trim($cert), ['size' => 8], ['spaceAfter' => 60]);
            }
        }

        // Right Column
        $right = $table->addCell(7000, ['valign' => 'top']);
        if (!empty($data['summary'])) {
            $right->addText('SUMMARY', ['bold' => true, 'color' => '000000'], ['borderBottomSize' => 6]);
            $right->addText($data['summary'], [], ['spaceAfter' => 150]);
        }

        foreach (['Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education'] as $title => $key) {
            if (!empty($data[$key])) {
                $right->addText(strtoupper($title), ['bold' => true, 'color' => '000000'], ['borderBottomSize' => 6]);
                foreach ($data[$key] as $item) {
                    $right->addText($item['title'] ?? $item['degree'] ?? $item['institution'] ?? '', ['bold' => true]);
                    $sub = $item['company'] ?? $item['institution'] ?? '';
                    if (!empty($item['date'])) $sub .= ' | ' . $item['date'];
                    $right->addText($sub, ['italic' => true, 'size' => 8], ['spaceAfter' => 0]);
                    
                    if (!empty($item['description'])) {
                        $lines = explode("\n", $item['description']);
                        foreach ($lines as $line) {
                            $clean = trim(ltrim(trim($line), '-*• '));
                            if (!empty($clean)) $right->addListItem($clean, 0, null, \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED, ['spaceAfter' => 0]);
                        }
                    }
                    $right->addTextBreak(1, null, ['spaceAfter' => 60]);
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