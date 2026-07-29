<?php

$dir = __DIR__ . '/../resources/views';

$replacements = [
    // RGBA and Hex
    'rgba(99, 102, 241,' => 'rgba(37, 99, 235,',
    'rgba(99,102,241,'  => 'rgba(37,99,235,',
    'rgba(79, 70, 229,'  => 'rgba(29, 78, 216,',
    'rgba(79,70,229,'   => 'rgba(29,78,216,',
    'rgba(139,92,246,'  => 'rgba(14,165,233,',
    'rgba(139, 92, 246,' => 'rgba(14, 165, 233,',
    '#818cf8' => '#60a5fa',
    '#c4b5fd' => '#7dd3fc',
    'prose-indigo' => 'prose-blue',
    'prose-purple' => 'prose-sky',
    'prose-violet' => 'prose-cyan',
    '// Indigo' => '// Blue',
];

$fileCount = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    if ($file->getExtension() !== 'php') continue;

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $oldContent = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        $fileCount++;
        $relPath = str_replace(dirname(__DIR__) . '\\', '', $filePath);
        echo "Updated: $relPath\n";
    }
}

echo "Done inline/RGBA updates! Total files updated: $fileCount\n";
