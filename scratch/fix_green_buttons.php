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

    // Replace green buttons to blue buttons
    $replacements = [
        'bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 active:scale-95 transition-all shadow-md shadow-emerald-600/20' => 
            'bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20',
        'bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20' =>
            'bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20',
        'bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold shadow-md shadow-emerald-600/20' =>
            'bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-bold shadow-md shadow-blue-600/20',
        'bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-600/20' =>
            'bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-600/20',
        'shadow-emerald-600/20' => 'shadow-blue-600/20',
        'shadow-teal-600/20' => 'shadow-blue-600/20',
        'shadow-green-600/20' => 'shadow-blue-600/20',
    ];

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        $fileCount++;
        $relPath = str_replace(dirname(__DIR__) . '\\', '', $filePath);
        echo "Updated green action buttons to blue in: $relPath\n";
    }
}

echo "Done! Total files updated: $fileCount\n";
