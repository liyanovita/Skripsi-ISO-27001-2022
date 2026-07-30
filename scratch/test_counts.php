<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;

echo "=== ISO STANDARDS TABLE ===\n";
echo "Total ISO Standards in DB: " . IsoStandard::count() . "\n";
echo "Annex A Standards in DB (code starts with A.): " . IsoStandard::where('code', 'LIKE', 'A.%')->count() . "\n";
echo "Clause Standards in DB (code not starting with A.): " . IsoStandard::where('code', 'NOT LIKE', 'A.%')->count() . "\n";

echo "\n=== SESSIONS LIST & COUNTS ===\n";
foreach (AssessmentSession::all() as $s) {
    $results = AssessmentResult::with('standard')->where('session_id', $s->id)->get();
    $applicable = $results->where('is_applicable', true);
    $excluded = $results->where('is_applicable', false);
    $annexA = $results->filter(fn($r) => str_starts_with($r->standard->code ?? '', 'A.'));
    $annexAApp = $annexA->where('is_applicable', true);
    $annexAExcl = $annexA->where('is_applicable', false);
    
    echo "Session #{$s->id} - {$s->name} (Status: {$s->status}):\n";
    echo "  - Total Results: {$results->count()}\n";
    echo "  - Applicable Total: {$applicable->count()}\n";
    echo "  - Excluded Total: {$excluded->count()}\n";
    echo "  - Annex A Total: {$annexA->count()}\n";
    echo "  - Annex A Applicable: {$annexAApp->count()}\n";
    echo "  - Annex A Excluded: {$annexAExcl->count()}\n";
    echo "  - Results maturity < 5: " . $applicable->where('maturity_rating', '<', 5)->count() . "\n";
    echo "  - Results maturity <= 3: " . $applicable->where('maturity_rating', '<=', 3)->count() . "\n";
    echo "  - Annex A maturity < 5: " . $annexAApp->where('maturity_rating', '<', 5)->count() . "\n";
    echo "  - Annex A maturity <= 3: " . $annexAApp->where('maturity_rating', '<=', 3)->count() . "\n";
    echo "-----------------------------------------------------\n";
}
