<?php

$dir = __DIR__ . '/../resources/views';

function scanDirectory($dir) {
    $results = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (preg_match_all('/(indigo|purple|violet)(-\d+|-[a-z0-9\/]+)?/i', $content, $matches)) {
                $results[$file->getPathname()] = array_unique($matches[0]);
            }
        }
    }
    return $results;
}

$matches = scanDirectory($dir);
echo "Found purple/indigo/violet in " . count($matches) . " files:\n";
foreach ($matches as $filepath => $colors) {
    $rel = str_replace(dirname(__DIR__) . '\\', '', $filepath);
    echo "- $rel: " . implode(', ', $colors) . "\n";
}
