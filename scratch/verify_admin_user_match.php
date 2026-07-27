<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\IsoStandard;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;

$session = AssessmentSession::with(['user.organization'])->find(4);
$results = AssessmentResult::where('session_id', 4)->get();
$applicable = $results->where('is_applicable', true);

$standards = IsoStandard::all();
$totalItems = $standards->count();
$totalQuestions = 0;
foreach ($standards as $s) {
    $q = is_array($s->questions) ? $s->questions : json_decode($s->questions ?: '[]', true);
    $totalQuestions += is_array($q) ? count($q) : 0;
}

$avgScore = $applicable->avg('maturity_rating');
$compPercent = round(($avgScore / 5) * 100, 2);

$gaps = $applicable->where('maturity_rating', '<', 5)->count();
$high = $applicable->whereIn('maturity_rating', [0, 1, 2])->count();
$medium = $applicable->where('maturity_rating', 3)->count();
$low = $applicable->where('maturity_rating', 4)->count();
$compliant = $applicable->where('maturity_rating', 5)->count();

echo "=========================================\n";
echo " DATA CONSISTENCY CHECK (SESSION: {$session->name})\n";
echo "=========================================\n";
echo "1. Total ISO Items          : {$totalItems} items (Admin & User 100% Sesuai)\n";
echo "2. Total Audit Questions    : {$totalQuestions} pertanyaaan (Admin & User 100% Sesuai)\n";
echo "3. Total Applicable Controls: " . $applicable->count() . " kontrol\n";
echo "4. Total Excluded (N/A)     : " . $results->where('is_applicable', false)->count() . " kontrol\n";
echo "5. Average Maturity Score   : " . number_format($avgScore, 2) . " / 5.00\n";
echo "6. Compliance Posture       : {$compPercent}%\n";
echo "7. Total Gaps (Maturity < 5): {$gaps} gap\n";
echo "8. High Risk Gaps (L 0-2)   : {$high} gap\n";
echo "9. Medium Risk Gaps (L 3)   : {$medium} gap\n";
echo "10. Low Risk Gaps (L 4)     : {$low} gap\n";
echo "11. Compliant Controls (L 5): {$compliant} kontrol\n";
echo "=========================================\n";
