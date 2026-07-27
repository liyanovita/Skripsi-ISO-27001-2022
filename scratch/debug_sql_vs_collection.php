<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;

$session = AssessmentSession::orderByDesc('updated_at')->first();

$sqlResults = AssessmentResult::where('session_id', $session->id)
    ->where('is_applicable', true)
    ->where('maturity_rating', '<', 5)
    ->get();

echo "SQL Query Result Count: " . $sqlResults->count() . "\n";

$collectionResults = $session->results
    ->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0)
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5);

echo "Collection Result Count: " . $collectionResults->count() . "\n";

// Find difference
$sqlIds = $sqlResults->pluck('id')->toArray();
$colIds = $collectionResults->pluck('id')->toArray();

$inColNotInSql = array_diff($colIds, $sqlIds);
$inSqlNotInCol = array_diff($sqlIds, $colIds);

echo "In Collection but not in SQL: " . count($inColNotInSql) . "\n";
foreach ($inColNotInSql as $id) {
    $r = AssessmentResult::find($id);
    echo " - ID {$r->id}, Standard: {$r->standard?->code}, Rating: {$r->maturity_rating}, Status: {$r->status}, Applicable: {$r->is_applicable}\n";
}

echo "In SQL but not in Collection: " . count($inSqlNotInCol) . "\n";
foreach ($inSqlNotInCol as $id) {
    $r = AssessmentResult::find($id);
    echo " - ID {$r->id}, Standard: {$r->standard?->code}, Rating: {$r->maturity_rating}, Status: {$r->status}, Applicable: {$r->is_applicable}\n";
}
