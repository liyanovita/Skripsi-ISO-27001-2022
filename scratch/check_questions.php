<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\IsoStandard;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;

$standards = IsoStandard::all();
echo "Total ISO Standards (items): " . $standards->count() . "\n";

$totalQuestions = 0;
foreach ($standards as $s) {
    $q = is_array($s->questions) ? $s->questions : json_decode($s->questions ?: '[]', true);
    $totalQuestions += is_array($q) ? count($q) : 0;
}
echo "Total Questions across all ISO Standards: " . $totalQuestions . "\n";

// Check session 4 questions count:
$session = AssessmentSession::find(4);
if ($session) {
    $results = AssessmentResult::where('session_id', 4)->get();
    echo "Session 4 results count: " . $results->count() . "\n";
    $sessionQuestions = 0;
    foreach ($results as $r) {
        $q = is_array($r->standard->questions) ? $r->standard->questions : json_decode($r->standard->questions ?: '[]', true);
        $sessionQuestions += is_array($q) ? count($q) : 0;
    }
    echo "Session 4 total questions: " . $sessionQuestions . "\n";
}
