<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$completedSessions = \App\Models\AssessmentSession::where('status', 'completed')->get();

echo "=== COMPLETED SESSIONS ===" . PHP_EOL;
foreach ($completedSessions as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Score: {$s->overall_maturity_score}" . PHP_EOL;
}

$avgMaturity = $completedSessions->avg('overall_maturity_score');
$compliancePct = round(($avgMaturity / 5.0) * 100, 1);

echo "-----------------------------------" . PHP_EOL;
echo "Average Maturity Score : " . number_format($avgMaturity, 2) . " / 5.00" . PHP_EOL;
echo "Compliance Percentage  : " . $compliancePct . "%" . PHP_EOL;
