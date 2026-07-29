<?php

$dir = __DIR__ . '/../resources/views';

$replacements = [
    // Tailwinds indigo -> blue
    'indigo-950' => 'blue-950',
    'indigo-900' => 'blue-900',
    'indigo-800' => 'blue-800',
    'indigo-750' => 'blue-800',
    'indigo-700' => 'blue-700',
    'indigo-650' => 'blue-700',
    'indigo-600' => 'blue-600',
    'indigo-550' => 'blue-600',
    'indigo-500' => 'blue-500',
    'indigo-400' => 'blue-400',
    'indigo-300' => 'blue-300',
    'indigo-200' => 'blue-200',
    'indigo-100' => 'blue-100',
    'indigo-50'  => 'blue-50',
    
    // Tailwinds purple -> sky
    'purple-950' => 'sky-950',
    'purple-900' => 'sky-900',
    'purple-800' => 'sky-800',
    'purple-700' => 'sky-700',
    'purple-600' => 'sky-600',
    'purple-500' => 'sky-500',
    'purple-400' => 'sky-400',
    'purple-300' => 'sky-300',
    'purple-200' => 'sky-200',
    'purple-100' => 'sky-100',
    'purple-50'  => 'sky-50',

    // Tailwinds violet -> cyan
    'violet-950' => 'cyan-950',
    'violet-900' => 'cyan-900',
    'violet-800' => 'cyan-800',
    'violet-700' => 'cyan-700',
    'violet-600' => 'cyan-600',
    'violet-500' => 'cyan-500',
    'violet-400' => 'cyan-400',
    'violet-300' => 'cyan-300',
    'violet-200' => 'cyan-200',
    'violet-100' => 'cyan-100',
    'violet-50'  => 'cyan-50',

    // Hex codes
    '#6366f1' => '#3b82f6',
    '#4f46e5' => '#2563eb',
    '#4338ca' => '#1d4ed8',
    '#8b5cf6' => '#06b6d4',
    '#7c3aed' => '#0891b2',
    '#a855f7' => '#0284c7',
    '#9333ea' => '#0369a1',
];

$fileCount = 0;
$totalReplacements = 0;

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

    // Special string/key replacements for specific files
    if (strpos($filePath, 'results\\edit.blade.php') !== false || strpos($filePath, 'results/edit.blade.php') !== false) {
        $content = str_replace("'color' => 'violet'", "'color' => 'cyan'", $content);
        $content = str_replace("'color' => 'indigo'", "'color' => 'blue'", $content);
        $content = str_replace("'violet'  =>", "'cyan'    =>", $content);
        $content = str_replace("'indigo'  =>", "'blue'    =>", $content);
    }

    if (strpos($filePath, 'app.blade.php') !== false || strpos($filePath, 'admin.blade.php') !== false) {
        $content = str_replace("'indigo'", "'blue'", $content);
        $content = str_replace("'purple'", "'sky'", $content);
        $content = str_replace("'violet'", "'cyan'", $content);
    }

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        $fileCount++;
        $relPath = str_replace(dirname(__DIR__) . '\\', '', $filePath);
        echo "Updated: $relPath\n";
    }
}

// Also check controllers in app/Http/Controllers if any hardcoded 'indigo' color strings exist
$appDir = __DIR__ . '/../app';
$appIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir));
foreach ($appIterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $oldContent = $content;

    $content = str_replace("'indigo'", "'blue'", $content);
    $content = str_replace("'purple'", "'sky'", $content);
    $content = str_replace("'violet'", "'cyan'", $content);
    $content = str_replace('#6366f1', '#3b82f6', $content);
    $content = str_replace('#4f46e5', '#2563eb', $content);

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        $fileCount++;
        $relPath = str_replace(dirname(__DIR__) . '\\', '', $filePath);
        echo "Updated Controller/App file: $relPath\n";
    }
}

echo "Done! Total files updated: $fileCount\n";
