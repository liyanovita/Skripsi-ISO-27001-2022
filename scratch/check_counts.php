<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\IsoStandard;

$standards = IsoStandard::whereNotNull('questions')->get();
$totalQuestions = 0;

foreach ($standards as $std) {
    if (is_array($std->questions)) {
        $totalQuestions += count($std->questions);
    }
}

echo "Total standards with questions: " . $standards->count() . "\n";
echo "Total Questions Count: " . $totalQuestions . "\n";
