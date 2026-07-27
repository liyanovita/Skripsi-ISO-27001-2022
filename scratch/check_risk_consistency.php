<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;

$session = AssessmentSession::find(4);
$results = AssessmentResult::where('session_id', 4)->where('is_applicable', true)->get();

echo "Session 4 Total Applicable Results: " . $results->count() . "\n";

// Method A: By maturity_rating directly: Level 0-2 = High, Level 3 = Medium, Level 4 = Low
$highA = $results->whereIn('maturity_rating', [0, 1, 2])->count();
$medA  = $results->where('maturity_rating', 3)->count();
$lowA  = $results->where('maturity_rating', 4)->count();
echo "Method A (Maturity Rating [0-2, 3, 4]): High=$highA, Medium=$medA, Low=$lowA\n";

// Method B: By stored $r->risk_priority field:
$highB = $results->where('risk_priority', 'High')->count();
$medB  = $results->where('risk_priority', 'Medium')->count();
$lowB  = $results->where('risk_priority', 'Low')->count();
$nullB = $results->whereNull('risk_priority')->count();
echo "Method B (Stored risk_priority): High=$highB, Medium=$medB, Low=$lowB, Null=$nullB\n";

// Method C: By $r->calculated_risk_priority accessor:
$highC = $results->filter(fn($r) => $r->calculated_risk_priority === 'High')->count();
$medC  = $results->filter(fn($r) => $r->calculated_risk_priority === 'Medium')->count();
$lowC  = $results->filter(fn($r) => $r->calculated_risk_priority === 'Low')->count();
echo "Method C (calculated_risk_priority accessor): High=$highC, Medium=$medC, Low=$lowC\n";

// Method D: ReportController query logic:
$highD = $results->filter(fn($r) => $r->risk_priority === 'High' || (is_null($r->risk_priority) && $r->maturity_rating <= 2))->count();
$medD  = $results->filter(fn($r) => $r->risk_priority === 'Medium' || (is_null($r->risk_priority) && $r->maturity_rating == 3))->count();
$lowD  = $results->filter(fn($r) => $r->risk_priority === 'Low' || (is_null($r->risk_priority) && $r->maturity_rating == 4))->count();
echo "Method D (ReportController logic): High=$highD, Medium=$medD, Low=$lowD\n";
