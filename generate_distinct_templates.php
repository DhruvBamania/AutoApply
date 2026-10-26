<?php
// Generator script for 15 structurally diverse templates.

$bases = [
    // BASE 1: Minimal Centered (Templates 1-3)
    1 => <<<'PHP'
<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class {CLASS_NAME}
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('{FONT}');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(['marginLeft' => 800, 'marginRight' => 800, 'marginTop' => 800, 'marginBottom' => 800]);

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 18, 'color' => '{COLOR}', 'allCaps' => true], ['alignment' => Jc::CENTER]);
        if (!empty($data['job_title'])) {
            $section->addText($data['job_title'], ['size' => 12, 'color' => '555555'], ['alignment' => Jc::CENTER]);
        }
        $section->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

        $phpWord->addTableStyle('Base1Table', ['borderSize' => 1, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);

        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Certifications' => 'certifications', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $section->addText(strtoupper($title), ['bold' => true, 'size' => 11, 'color' => '{COLOR}'], ['borderBottomSize' => 6, 'spaceAfter' => 60, 'spaceBefore' => 120]);
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
PHP,

    // BASE 2: Two-Column Split (Templates 4-6)
    2 => <<<'PHP'
<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class {CLASS_NAME}
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('{FONT}');
        $phpWord->setDefaultFontSize(9);
        $section = $phpWord->addSection(['marginLeft' => 600, 'marginRight' => 600, 'marginTop' => 600, 'marginBottom' => 600]);

        $phpWord->addTableStyle('LayoutTable', ['borderSize' => 0, 'cellMargin' => 50]);
        $table = $section->addTable('LayoutTable');
        $table->addRow();

        // Left Column
        $left = $table->addCell(3000, ['bgColor' => 'f4f4f4']);
        $left->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 16, 'color' => '{COLOR}']);
        if (!empty($data['job_title'])) $left->addText($data['job_title'], ['bold' => true, 'size' => 10]);
        $left->addTextBreak(1);
        
        $left->addText('CONTACT', ['bold' => true, 'color' => '{COLOR}'], ['borderBottomSize' => 6]);
        $contacts = explode('|', $data['contact'] ?? '');
        foreach ($contacts as $c) {
            $left->addText(trim($c), ['size' => 8], ['spaceAfter' => 0]);
        }
        $left->addTextBreak(1);

        if (!empty($data['skills'])) {
            $left->addText('SKILLS', ['bold' => true, 'color' => '{COLOR}'], ['borderBottomSize' => 6]);
            $skills = is_array($data['skills']) ? $data['skills'] : explode(',', $data['skills']);
            foreach ($skills as $skill) {
                $left->addText(trim($skill), ['size' => 9], ['spaceAfter' => 0]);
            }
            $left->addTextBreak(1);
        }
        if (!empty($data['certifications'])) {
            $left->addText('CERTIFICATIONS', ['bold' => true, 'color' => '{COLOR}'], ['borderBottomSize' => 6]);
            $certs = is_array($data['certifications']) ? $data['certifications'] : explode(',', $data['certifications']);
            foreach ($certs as $cert) {
                $left->addText(trim($cert), ['size' => 8], ['spaceAfter' => 60]);
            }
        }

        // Right Column
        $right = $table->addCell(7000, ['valign' => 'top']);
        if (!empty($data['summary'])) {
            $right->addText('SUMMARY', ['bold' => true, 'color' => '{COLOR}'], ['borderBottomSize' => 6]);
            $right->addText($data['summary'], [], ['spaceAfter' => 150]);
        }

        foreach (['Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education'] as $title => $key) {
            if (!empty($data[$key])) {
                $right->addText(strtoupper($title), ['bold' => true, 'color' => '{COLOR}'], ['borderBottomSize' => 6]);
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
PHP,

    // BASE 3: Left-Aligned Block (Templates 7-9)
    3 => <<<'PHP'
<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class {CLASS_NAME}
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('{FONT}');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 22, 'color' => '{COLOR}']);
        if (!empty($data['job_title'])) $section->addText($data['job_title'], ['size' => 14, 'color' => '333333']);
        $section->addText($data['contact'] ?? '', ['size' => 9], ['spaceAfter' => 200]);

        $phpWord->addTableStyle('HeaderTable', ['borderSize' => 0, 'cellMargin' => 50, 'bgColor' => '{COLOR}']);
        
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
PHP,

    // BASE 4: Timeline / Right-Aligned Headers (Templates 10-12)
    4 => <<<'PHP'
<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class {CLASS_NAME}
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('{FONT}');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $section->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 24, 'color' => '{COLOR}'], ['alignment' => Jc::RIGHT]);
        if (!empty($data['job_title'])) $section->addText($data['job_title'], ['size' => 12], ['alignment' => Jc::RIGHT]);
        $section->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::RIGHT, 'spaceAfter' => 300, 'borderBottomSize' => 18, 'borderBottomColor' => '{COLOR}']);

        $phpWord->addTableStyle('TimelineTable', ['borderSize' => 0, 'cellMargin' => 50]);
        
        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $table = $section->addTable('TimelineTable');
                $table->addRow();
                $left = $table->addCell(2500, ['valign' => 'top']);
                $left->addText(strtoupper($title), ['bold' => true, 'color' => '{COLOR}', 'size' => 11], ['alignment' => Jc::RIGHT]);
                
                $right = $table->addCell(7500, ['valign' => 'top', 'borderLeftSize' => 6, 'borderLeftColor' => '{COLOR}']);
                
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
PHP,

    // BASE 5: Border Boxed / Cards (Templates 13-15)
    5 => <<<'PHP'
<?php
namespace App\Services\ResumeTemplates;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class {CLASS_NAME}
{
    public function generate(array $data, string $format = 'Word2007')
    {
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('{FONT}');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection();

        $phpWord->addTableStyle('CardHeader', ['borderSize' => 18, 'borderColor' => '{COLOR}', 'cellMargin' => 100, 'bgColor' => 'f8f9fa']);
        $table = $section->addTable('CardHeader');
        $table->addRow();
        $cell = $table->addCell(10000);
        $cell->addText($data['name'] ?? 'Your Name', ['bold' => true, 'size' => 20, 'color' => '{COLOR}'], ['alignment' => Jc::CENTER]);
        if (!empty($data['job_title'])) $cell->addText($data['job_title'], ['size' => 12], ['alignment' => Jc::CENTER]);
        $cell->addText($data['contact'] ?? '', ['size' => 9], ['alignment' => Jc::CENTER]);

        $section->addTextBreak(1);
        $phpWord->addTableStyle('CardBody', ['borderSize' => 6, 'borderColor' => '{COLOR}', 'cellMargin' => 100]);

        $sections = ['Summary' => 'summary', 'Experience' => 'experience', 'Projects' => 'projects', 'Education' => 'education', 'Skills' => 'skills'];
        foreach ($sections as $title => $key) {
            if (!empty($data[$key])) {
                $section->addText(strtoupper($title), ['bold' => true, 'color' => '{COLOR}', 'size' => 12], ['spaceAfter' => 60]);
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
PHP
];

$configs = [
    1 => ['base' => 1, 'name' => 'Template1Minimal', 'font' => 'Arial', 'color' => '2c3e50'],
    2 => ['base' => 1, 'name' => 'Template2Modern', 'font' => 'Calibri', 'color' => '2980b9'],
    3 => ['base' => 1, 'name' => 'Template3Executive', 'font' => 'Georgia', 'color' => '1e3a8a'],
    4 => ['base' => 2, 'name' => 'Template4Creative', 'font' => 'Verdana', 'color' => '8b5cf6'],
    5 => ['base' => 2, 'name' => 'Template5Tech', 'font' => 'Tahoma', 'color' => '16a085'],
    6 => ['base' => 2, 'name' => 'Template6Classic', 'font' => 'Times New Roman', 'color' => '000000'],
    7 => ['base' => 3, 'name' => 'Template7Elegant', 'font' => 'Garamond', 'color' => '8e44ad'],
    8 => ['base' => 3, 'name' => 'Template8Clean', 'font' => 'Arial', 'color' => '27ae60'],
    9 => ['base' => 3, 'name' => 'Template9Bold', 'font' => 'Helvetica', 'color' => 'c0392b'],
    10 => ['base' => 4, 'name' => 'Template10Startup', 'font' => 'Trebuchet MS', 'color' => 'f39c12'],
    11 => ['base' => 4, 'name' => 'Template11Corporate', 'font' => 'Arial', 'color' => '34495e'],
    12 => ['base' => 4, 'name' => 'Template12Academic', 'font' => 'Palatino', 'color' => '000000'],
    13 => ['base' => 5, 'name' => 'Template13Modern', 'font' => 'Verdana', 'color' => 'e67e22'],
    14 => ['base' => 5, 'name' => 'Template14Simple', 'font' => 'Calibri', 'color' => '1abc9c'],
    15 => ['base' => 5, 'name' => 'Template15Dynamic', 'font' => 'Tahoma', 'color' => 'd35400'],
];

foreach ($configs as $config) {
    $content = $bases[$config['base']];
    $content = str_replace('{CLASS_NAME}', $config['name'], $content);
    $content = str_replace('{FONT}', $config['font'], $content);
    $content = str_replace('{COLOR}', $config['color'], $content);
    
    file_put_contents(__DIR__ . "/app/Services/ResumeTemplates/{$config['name']}.php", $content);
    echo "Generated {$config['name']}.php\n";
}
