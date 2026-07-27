<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;

$session = AssessmentSession::orderByDesc('updated_at')->first();
if ($session) {
    echo "Restoring assessable controls for Session ID: " . $session->id . " (" . $session->name . ")\n";
    
    // Delete dummy results for parent headers with no questions
    $parentHeaderIds = IsoStandard::whereNull('questions')
        ->orWhere('questions', '[]')
        ->orWhere('questions', 'null')
        ->pluck('id');
        
    AssessmentResult::where('session_id', $session->id)
        ->whereIn('iso_standard_id', $parentHeaderIds)
        ->delete();
        
    // Reset excluded controls to exactly 3
    $excludedCodes = ['A.5.30', 'A.8.23', 'A.8.31'];
    AssessmentResult::where('session_id', $session->id)->update(['is_applicable' => true]);
    AssessmentResult::where('session_id', $session->id)
        ->whereHas('standard', fn($q) => $q->whereIn('code', $excludedCodes))
        ->update(['is_applicable' => false]);
        
    $session->calculateMaturityScore();
    
    $rawResults = $session->results()->with('standard')->get();
    $assessable = $rawResults->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
    
    $active = $assessable->where('is_applicable', true);
    $excluded = $assessable->where('is_applicable', false);
    $level5 = $active->where('status', 'completed')->where('maturity_rating', 5)->count();
    $gaps = $active->where('status', 'completed')->where('maturity_rating', '<', 5)->count();
    $highRisk = $active->where('status', 'completed')->where(fn($r) => $r->maturity_rating <= 1 || $r->calculated_risk_priority === 'High')->count();
    
    echo "Restored Session " . $session->id . " stats:\n";
    echo " - Total Assessable Results: " . $assessable->count() . "\n";
    echo " - Applicable Controls: " . $active->count() . "\n";
    echo " - Excluded Controls: " . $excluded->count() . "\n";
    echo " - Level 5 Controls: " . $level5 . "\n";
    echo " - Total Gaps (Maturity < 5): " . $gaps . "\n";
    echo " - High Risk Gaps: " . $highRisk . "\n";
}
