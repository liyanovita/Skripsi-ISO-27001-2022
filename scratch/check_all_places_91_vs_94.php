<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\User;

$user = User::where('role', 'user')->first() ?? User::first();

echo "=== DIAGNOSING ALL QUERY PLACES FOR USER {$user->id} ===\n\n";

// 1. AppServiceProvider (Sidebar)
$sidebarQuery = AssessmentResult::whereHas('session', fn($q) => $q->where('user_id', $user->id))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "1. Sidebar query (AppServiceProvider): " . $sidebarQuery . "\n";

// 2. Latest Session ID
$latestSession = AssessmentSession::where('user_id', $user->id)->orderByDesc('updated_at')->first();
echo "Latest Session ID: " . $latestSession->id . " (" . $latestSession->name . ")\n";

// 3. Strategic Analytics (AnalyticsService)
$rawResults = $latestSession->results()->with('standard')->get();
$assessableResults = $rawResults->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);

$strategicAllSession = $rawResults->where('is_applicable', true)->where('status', 'completed')->where('maturity_rating', '<', 5)->count();
$strategicAssessableOnly = $assessableResults->where('is_applicable', true)->where('status', 'completed')->where('maturity_rating', '<', 5)->count();

echo "2a. Strategic (using all results in session): " . $strategicAllSession . "\n";
echo "2b. Strategic (using assessable standard with questions): " . $strategicAssessableOnly . "\n";

// 4. Workspace Index (WorkspaceService)
$workspaceQuery = AssessmentResult::whereHas('session', fn($q) => $q->where('user_id', $user->id))
    ->where('is_applicable', true)
    ->where('maturity_rating', '<', 5)
    ->count();
echo "3. Workspace query: " . $workspaceQuery . "\n";

// 5. Admin Export (AssessmentReportExport)
$exportQuery = AssessmentResult::whereHas('session', fn($q) => $q->where('user_id', $user->id))
    ->where('is_applicable', true)
    ->where('maturity_rating', '<', 5)
    ->count();
echo "4. Admin Excel Export query: " . $exportQuery . "\n";

// Break down by sessions if user has multiple sessions!
$allSessions = AssessmentSession::where('user_id', $user->id)->get();
echo "\n--- SESSIONS BREAKDOWN ---\n";
foreach ($allSessions as $s) {
    $count = AssessmentResult::where('session_id', $s->id)->where('is_applicable', true)->where('status', 'completed')->where('maturity_rating', '<', 5)->count();
    echo "Session {$s->id} ({$s->name}): {$count} gaps (updated: {$s->updated_at})\n";
}
