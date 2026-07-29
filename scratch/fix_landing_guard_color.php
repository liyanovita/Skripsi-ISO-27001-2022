<?php

$files = [
    __DIR__ . '/../resources/views/pages/landing.blade.php',
    __DIR__ . '/../resources/views/pages/workspace/soa_pdf.blade.php',
    __DIR__ . '/../resources/views/pages/reports/pdf_template.blade.php',
    __DIR__ . '/../resources/views/admin/reports/pdf_template.blade.php',
    __DIR__ . '/../resources/views/pages/kb/pdf.blade.php',
    __DIR__ . '/../resources/views/layouts/app.blade.php',
    __DIR__ . '/../resources/views/layouts/admin.blade.php',
];

$replacements = [
    '<span style="color: #0891b2;">Guard</span>' => '<span style="color: #0284c7;">Guard</span>',
    '<span style="color: #008B9B;">Guard</span>' => '<span style="color: #0284c7;">Guard</span>',
    '<span style="color: #a78bfa;">Guard</span>' => '<span style="color: #38bdf8;">Guard</span>',
];

foreach ($files as $filePath) {
    if (!file_exists($filePath)) continue;
    $content = file_get_contents($filePath);
    $oldContent = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if (strpos($filePath, 'landing.blade.php') !== false) {
        $content = str_replace('from-green-50', 'from-sky-50', $content);
        $content = str_replace('border-green-100', 'border-sky-100', $content);
        $content = str_replace('hover:border-green-200', 'hover:border-sky-200', $content);
        $content = str_replace('bg-green-100', 'bg-sky-100', $content);
        $content = str_replace('text-green-600', 'text-sky-600', $content);
        $content = str_replace('text-green-700', 'text-sky-700', $content);
        $content = str_replace('group-hover:bg-green-600', 'group-hover:bg-sky-600', $content);
        $content = str_replace('from-emerald-500 to-teal-500', 'from-blue-600 to-sky-500', $content);
    }

    if ($content !== $oldContent) {
        file_put_contents($filePath, $content);
        echo "Updated Guard logo color in: " . basename($filePath) . "\n";
    }
}

echo "Done!\n";
