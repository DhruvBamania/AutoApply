<?php
$base = file_get_contents(__DIR__ . '/app/Services/ResumeTemplates/Template1Minimal.php');

$variations = [
    5 =>  ['name' => 'Template5Tech',      'font' => 'Verdana',         'color' => '2c3e50', 'headerStyle' => "['bold' => true, 'size' => 12, 'color' => '2c3e50', 'allCaps' => true]"],
    6 =>  ['name' => 'Template6Classic',   'font' => 'Times New Roman', 'color' => '000000', 'headerStyle' => "['bold' => true, 'size' => 13, 'allCaps' => true, 'borderBottomSize' => 12]"],
    7 =>  ['name' => 'Template7Elegant',   'font' => 'Garamond',        'color' => '8e44ad', 'headerStyle' => "['bold' => true, 'size' => 12, 'color' => '8e44ad', 'allCaps' => true, 'borderBottomSize' => 6]"],
    8 =>  ['name' => 'Template8Clean',     'font' => 'Tahoma',          'color' => '16a085', 'headerStyle' => "['bold' => true, 'size' => 11, 'color' => '16a085', 'allCaps' => true]"],
    9 =>  ['name' => 'Template9Bold',      'font' => 'Arial',           'color' => 'c0392b', 'headerStyle' => "['bold' => true, 'size' => 12, 'color' => 'c0392b', 'allCaps' => true, 'borderBottomSize' => 18]"],
    10 => ['name' => 'Template10Startup',  'font' => 'Helvetica',       'color' => 'f39c12', 'headerStyle' => "['bold' => true, 'size' => 11, 'color' => 'f39c12', 'allCaps' => true]"],
    11 => ['name' => 'Template11Corporate','font' => 'Calibri',         'color' => '2980b9', 'headerStyle' => "['bold' => true, 'size' => 12, 'color' => '2980b9', 'allCaps' => true, 'borderBottomSize' => 12]"],
    12 => ['name' => 'Template12Academic', 'font' => 'Palatino',        'color' => '000000', 'headerStyle' => "['bold' => true, 'size' => 13, 'smallCaps' => true, 'borderBottomSize' => 6]"],
    13 => ['name' => 'Template13Modern',   'font' => 'Trebuchet MS',    'color' => '34495e', 'headerStyle' => "['bold' => true, 'size' => 12, 'color' => '34495e', 'allCaps' => true]"],
    14 => ['name' => 'Template14Simple',   'font' => 'Arial',           'color' => '27ae60', 'headerStyle' => "['bold' => true, 'size' => 11, 'color' => '27ae60', 'allCaps' => true, 'borderBottomSize' => 6]"],
    15 => ['name' => 'Template15Dynamic',  'font' => 'Verdana',         'color' => 'd35400', 'headerStyle' => "['bold' => true, 'size' => 11, 'color' => 'd35400', 'allCaps' => true, 'borderBottomSize' => 12]"]
];

foreach ($variations as $num => $var) {
    $content = $base;
    $content = str_replace('Template1Minimal', $var['name'], $content);
    $content = preg_replace("/setDefaultFontName\('.*?'\)/", "setDefaultFontName('{$var['font']}')", $content);
    
    // Replace section headers
    $content = preg_replace("/\['bold' => true, 'size' => 11\], \['borderBottomSize' => 6, 'spaceAfter' => 60, 'spaceBefore' => 120\]/", "{$var['headerStyle']}, ['spaceAfter' => 60, 'spaceBefore' => 120]", $content);
    
    // Name color
    $content = preg_replace("/\['bold' => true, 'size' => 16\]/", "['bold' => true, 'size' => 18, 'color' => '{$var['color']}']", $content);
    
    // File name
    $content = str_replace("uniqid('template1_')", "uniqid('template{$num}_')", $content);

    file_put_contents(__DIR__ . "/app/Services/ResumeTemplates/{$var['name']}.php", $content);
    echo "Created {$var['name']}.php\n";
}
