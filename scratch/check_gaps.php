<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\Organization;

$org = Organization::where('code', 'KPK')->orWhere('name', 'like', '%KPK%')->first();
echo "Organization: " . ($org ? $org->name . " (ID: {$org->id})" : "Not found") . "\n";

$sessions = AssessmentSession::where('organization_id', $org->id)->get();
echo "Total Sessions: " . $sessions->count() . "\n";

foreach ($sessions as $s) {
    echo "Session ID: {$s->id}, Name: {$s->name}, Status: {$s->status}, User ID: {$s->user_id}\n";
    $results = AssessmentResult::where('session_id', $s->id)->where('is_applicable', true)->get();
    echo "  Applicable results count: " . $results->count() . "\n";
    echo "  Maturity < 5 count: " . $results->where('maturity_rating', '<', 5)->count() . "\n";
    echo "  Maturity < 4 count: " . $results->where('maturity_rating', '<', 4)->count() . "\n";
    echo "  Maturity Level 0-2 (High Risk): " . $results->whereIn('maturity_rating', [0, 1, 2])->count() . "\n";
    echo "  Maturity Level 3 (Medium Risk): " . $results->where('maturity_rating', 3)->count() . "\n";
    echo "  Maturity Level 4 (Low Risk): " . $results->where('maturity_rating', 4)->count() . "\n";
    echo "  Maturity Level 5 (Compliant): " . $results->where('maturity_rating', 5)->count() . "\n";
}

// Check DashboardService query logic:
// DashboardService queries results for auth user:
$latestSession = $sessions->where('status', 'completed')->sortByDesc('updated_at')->first() ?? $sessions->first();
if ($latestSession) {
    echo "\n--- Latest Session Results ({$latestSession->name}) ---\n";
    $latestResults = AssessmentResult::where('session_id', $latestSession->id)
        ->where('is_applicable', true)
        ->get();
    
    $c012 = $latestResults->whereIn('maturity_rating', [0, 1, 2])->count();
    $c3 = $latestResults->where('maturity_rating', 3)->count();
    $c4 = $latestResults->where('maturity_rating', 4)->count();
    $c5 = $latestResults->where('maturity_rating', 5)->count();
    
    echo "Level 0-2 (High): $c012\n";
    echo "Level 3 (Medium): $c3\n";
    echo "Level 4 (Low): $c4\n";
    echo "Level 5 (Optimized): $c5\n";
    echo "Total Applicable Controls: " . $latestResults->count() . "\n";
    echo "Total Gaps (Maturity < 5): " . ($c012 + $c3 + $c4) . "\n";
    echo "Total Gaps (Maturity < 4): " . ($c012 + $c3) . "\n";
}
