<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;

$totalStandards = IsoStandard::whereNotNull('questions')->where('questions', '!=', '[]')->where('questions', '!=', 'null')->count();
echo "Total ISO Standards with questions: " . $totalStandards . "\n";

$session = AssessmentSession::orderByDesc('updated_at')->first();
if ($session) {
    echo "Session ID: " . $session->id . " (" . $session->name . ")\n";
    $existingStandards = $session->results->pluck('iso_standard_id')->toArray();
    $missingStandards = IsoStandard::whereNotNull('questions')
        ->where('questions', '!=', '[]')
        ->where('questions', '!=', 'null')
        ->whereNotIn('id', $existingStandards)
        ->get();
        
    echo "Missing Standards count for Session " . $session->id . ": " . $missingStandards->count() . "\n";
    
    foreach ($missingStandards as $std) {
        echo " - Missing: " . $std->code . " (" . $std->title . ")\n";
    }
}
