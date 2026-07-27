<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;

$session = AssessmentSession::orderByDesc('updated_at')->first();
if ($session) {
    $results = $session->results->where('is_applicable', true)->where('status', 'completed');
    $totalGaps = $results->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5)->count();
    $highRisk = $results->filter(fn($r) => $r->maturity_rating <= 1 || $r->calculated_risk_priority === 'High')->count();
    $mediumLowRisk = max(0, $totalGaps - $highRisk);
    
    echo "Session ID: " . $session->id . " (" . $session->name . ")\n";
    echo "Total Gaps (Maturity < 5, non-null): " . $totalGaps . "\n";
    echo "High Risk Gaps: " . $highRisk . "\n";
    echo "Medium/Low Risk Gaps: " . $mediumLowRisk . "\n";
}
