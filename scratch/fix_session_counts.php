<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;

$session = AssessmentSession::orderByDesc('updated_at')->first();
if ($session) {
    AssessmentResult::where('session_id', $session->id)->update(['is_applicable' => true]);
    
    $excludedCodes = ['A.5.30', 'A.8.23', 'A.8.31'];
    AssessmentResult::where('session_id', $session->id)
        ->whereHas('standard', fn($q) => $q->whereIn('code', $excludedCodes))
        ->update(['is_applicable' => false]);
        
    $session->calculateMaturityScore();
    
    $appCount = AssessmentResult::where('session_id', $session->id)->where('is_applicable', true)->count();
    $exCount = AssessmentResult::where('session_id', $session->id)->where('is_applicable', false)->count();
    $totCount = AssessmentResult::where('session_id', $session->id)->count();
    
    echo "Final Session " . $session->id . " counts:\n";
    echo " - Total Results: " . $totCount . " / 137\n";
    echo " - Applicable: " . $appCount . " / 134\n";
    echo " - Excluded: " . $exCount . " / 3\n";
}
