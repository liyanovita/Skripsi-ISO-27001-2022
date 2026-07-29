<?php

$dir = __DIR__ . '/../resources/views';

$fileCount = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    if ($file->getExtension() !== 'php') continue;

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $oldContent = $content;

    // Replace cyan classes to blue classes
    $replacements = [
        'bg-cyan-950' => 'bg-blue-950',
        'bg-cyan-900' => 'bg-blue-900',
        'bg-cyan-800' => 'bg-blue-800',
        'bg-cyan-700' => 'bg-blue-700',
        'bg-cyan-600' => 'bg-blue-600',
        'bg-cyan-500' => 'bg-blue-500',
        'bg-cyan-400' => 'bg-blue-400',
        'bg-cyan-300' => 'bg-blue-300',
        'bg-cyan-200' => 'bg-blue-200',
        'bg-cyan-100' => 'bg-blue-100',
        'bg-cyan-50'  => 'bg-blue-50',

        'hover:bg-cyan-700' => 'hover:bg-blue-700',
        'hover:bg-cyan-800' => 'hover:bg-blue-800',
        'hover:bg-cyan-600' => 'hover:bg-blue-600',
        'hover:bg-cyan-50'  => 'hover:bg-blue-50',
        'hover:bg-cyan-100' => 'hover:bg-blue-100',

        'text-cyan-950' => 'text-blue-950',
        'text-cyan-900' => 'text-blue-900',
        'text-cyan-800' => 'text-blue-800',
        'text-cyan-700' => 'text-blue-700',
        'text-cyan-600' => 'text-blue-600',
        'text-cyan-500' => 'text-blue-500',
        'text-cyan-400' => 'text-blue-400',
        'text-cyan-300' => 'text-blue-300',
        'text-cyan-200' => 'text-blue-200',
        'text-cyan-100' => 'text-blue-100',

        'hover:text-cyan-600' => 'hover:text-blue-600',
        'hover:text-cyan-700' => 'hover:text-blue-700',
        'group-hover:text-cyan-600' => 'group-hover:text-blue-600',

        'border-cyan-500' => 'border-blue-500',
        'border-cyan-400' => 'border-blue-400',
        'border-cyan-300' => 'border-blue-300',
        'border-cyan-200' => 'border-blue-200',
        'border-cyan-100' => 'border-blue-100',

        'shadow-cyan-600/20' => 'shadow-blue-600/20',
        'shadow-cyan-500/20' => 'shadow-blue-500/20',
        'shadow-cyan-600/30' => 'shadow-blue-600/30',

        'from-cyan-700' => 'from-blue-700',
        'from-cyan-600' => 'from-blue-600',
        'from-cyan-500' => 'from-blue-500',
        'to-cyan-700'   => 'to-blue-700',
        'to-cyan-600'   => 'to-blue-600',
        'to-cyan-500'   => 'to-blue-500',

        'ring-cyan-600' => 'ring-blue-600',
        'ring-cyan-500' => 'ring-blue-500',
    ];

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        $fileCount++;
        $relPath = str_replace(dirname(__DIR__) . '\\', '', $filePath);
        echo "Updated Cyan -> Blue in: $relPath\n";
    }
}

echo "Done replacing Cyan with Blue! Total files updated: $fileCount\n";
