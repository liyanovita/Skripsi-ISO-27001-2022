<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;

$session = AssessmentSession::find(4);
$results = AssessmentResult::with('standard')->where('session_id', 4)->get();

echo "Total Results in Session 4: " . $results->count() . "\n";
echo "Applicable Results: " . $results->where('is_applicable', true)->count() . "\n";
echo "Excluded Results: " . $results->where('is_applicable', false)->count() . "\n";

// Annex A vs Clauses
$annexResults = $results->filter(fn($r) => str_starts_with($r->standard->code, 'A.'));
$clauseResults = $results->filter(fn($r) => !str_starts_with($r->standard->code, 'A.'));

echo "\n--- Annex A Controls (Total: {$annexResults->count()}) ---\n";
echo "Annex A Applicable: " . $annexResults->where('is_applicable', true)->count() . "\n";
echo "Annex A Excluded: " . $annexResults->where('is_applicable', false)->count() . "\n";
echo "Annex A < 5 (Gaps): " . $annexResults->where('is_applicable', true)->where('maturity_rating', '<', 5)->count() . "\n";
echo "Annex A < 4 (Gaps below Compliant): " . $annexResults->where('is_applicable', true)->where('maturity_rating', '<', 4)->count() . "\n";
echo "Annex A Non-Compliant (Level 0-1): " . $annexResults->where('is_applicable', true)->where('maturity_rating', '<', 2)->count() . "\n";
echo "Annex A Partially Compliant (Level 2-3): " . $annexResults->where('is_applicable', true)->whereIn('maturity_rating', [2, 3])->count() . "\n";
echo "Annex A Non-Compliant + Partially Compliant (Level 0-3): " . $annexResults->where('is_applicable', true)->where('maturity_rating', '<=', 3)->count() . "\n";
echo "Annex A Level 0-2 (High Risk): " . $annexResults->where('is_applicable', true)->whereIn('maturity_rating', [0, 1, 2])->count() . "\n";
echo "Annex A Level 3 (Medium Risk): " . $annexResults->where('is_applicable', true)->where('maturity_rating', 3)->count() . "\n";
echo "Annex A Level 4 (Low Risk): " . $annexResults->where('is_applicable', true)->where('maturity_rating', 4)->count() . "\n";
echo "Annex A Level 5 (Compliant): " . $annexResults->where('is_applicable', true)->where('maturity_rating', 5)->count() . "\n";

echo "\n--- All Controls (Annex A + Clauses) ---\n";
echo "All < 5: " . $results->where('is_applicable', true)->where('maturity_rating', '<', 5)->count() . "\n";
echo "All Level 0-3: " . $results->where('is_applicable', true)->where('maturity_rating', '<=', 3)->count() . "\n";
echo "All Level 0-2 (High Risk): " . $results->where('is_applicable', true)->whereIn('maturity_rating', [0, 1, 2])->count() . "\n";
echo "All Level 3: " . $results->where('is_applicable', true)->where('maturity_rating', 3)->count() . "\n";
echo "All Level 4: " . $results->where('is_applicable', true)->where('maturity_rating', 4)->count() . "\n";

// Let's check total Annex A controls (93 Annex A controls in ISO 27001:2022)
echo "\n--- Check 94 number derivation ---\n";
echo "93 Annex A controls + ? = 94\n";
echo "Annex A Level 0-4 count: " . $annexResults->where('is_applicable', true)->where('maturity_rating', '<', 5)->count() . "\n";
echo "Annex A Level 0-3 count + Excluded = " . ($annexResults->where('is_applicable', true)->where('maturity_rating', '<=', 3)->count() + $annexResults->where('is_applicable', false)->count()) . "\n";

// Check if any query in the codebase yields 94:
$adminReportTotalGaps = AssessmentResult::join('assessment_sessions', 'assessment_results.session_id', '=', 'assessment_sessions.id')
    ->where('assessment_results.is_applicable', true)
    ->where('assessment_results.maturity_rating', '<', 5)
    ->where('assessment_sessions.status', 'completed')
    ->count();
echo "Admin Report Total Gaps (maturity < 5 across completed sessions): " . $adminReportTotalGaps . "\n";

$adminReportNonCompliantGaps = AssessmentResult::join('assessment_sessions', 'assessment_results.session_id', '=', 'assessment_sessions.id')
    ->where('assessment_results.is_applicable', true)
    ->where('assessment_results.maturity_rating', '<', 4)
    ->where('assessment_sessions.status', 'completed')
    ->count();
echo "Admin Report Gaps (maturity < 4 across completed sessions): " . $adminReportNonCompliantGaps . "\n";
