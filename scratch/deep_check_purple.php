<?php

$dirs = [
    __DIR__ . '/../resources',
    __DIR__ . '/../app',
    __DIR__ . '/../public',
];

$results = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if (!$file->isFile()) continue;
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['php', 'blade', 'js', 'css'])) continue;
        
        $content = file_get_contents($file->getPathname());
        
        // Match indigo/purple/violet words and hex colors commonly used for purple
        // #6366f1 (indigo-500), #4f46e5 (indigo-600), #8b5cf6 (violet-500), #a855f7 (purple-500), #7c3aed (violet-600)
        if (preg_match_all('/(indigo|purple|violet|#6366f1|#4f46e5|#8b5cf6|#a855f7|#7c3aed)/i', $content, $matches)) {
            $rel = str_replace(dirname(__DIR__) . '/', '', str_replace('\\', '/', $file->getPathname()));
            $results[$rel] = array_count_values(array_map('strtolower', $matches[0]));
        }
    }
}

echo "Scan complete. Found " . count($results) . " files:\n";
foreach ($results as $file => $counts) {
    $str = [];
    foreach ($counts as $k => $v) {
        $str[] = "$k ($v)";
    }
    echo "- $file: " . implode(', ', $str) . "\n";
}
