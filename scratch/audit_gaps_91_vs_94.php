<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\User;

$user = User::where('role', 'user')->first() ?? User::first();
echo "User ID: " . $user->id . " (" . $user->name . ")\n\n";

$sessions = AssessmentSession::where('user_id', $user->id)->get();
echo "Total User Sessions: " . $sessions->count() . "\n";
foreach ($sessions as $s) {
    echo " - Session ID " . $s->id . ": " . $s->name . " (status: " . $s->status . ")\n";
}

echo "\n--- QUERY BREAKDOWN ---\n";

// Query A: All completed assessment results across all sessions for user where maturity < 5
$qA = AssessmentResult::whereHas('session', fn($q) => $q->where('user_id', $user->id))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "Query A (All sessions, applicable, completed, maturity < 5): " . $qA . "\n";

// Query B: Latest session only, applicable, completed, maturity < 5
$latestSession = AssessmentSession::where('user_id', $user->id)->orderByDesc('updated_at')->first();
$qB = AssessmentResult::where('session_id', $latestSession->id)
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "Query B (Latest session only, applicable, completed, maturity < 5): " . $qB . "\n";

// Query C: Latest session only, applicable, completed, maturity <= 4 vs < 4 vs <= 3
$qC_3 = AssessmentResult::where('session_id', $latestSession->id)
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<=', 3)
    ->count();
echo "Query C (Latest session, maturity <= 3 / Non-Compliant + Partial): " . $qC_3 . "\n";

// Query D: Filter by standard type / questions
$qD_assessable = AssessmentResult::where('session_id', $latestSession->id)
    ->whereHas('standard', fn($q) => $q->whereNotNull('questions')->where('questions', '!=', '[]')->where('questions', '!=', 'null'))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "Query D (Latest session, assessable standard with questions, maturity < 5): " . $qD_assessable . "\n";

$qD_assessable_3 = AssessmentResult::where('session_id', $latestSession->id)
    ->whereHas('standard', fn($q) => $q->whereNotNull('questions')->where('questions', '!=', '[]')->where('questions', '!=', 'null'))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<=', 3)
    ->count();
echo "Query D2 (Latest session, assessable standard with questions, maturity <= 3): " . $qD_assessable_3 . "\n";

// Query E: Annex A controls only vs Main Clauses
$annexGaps = AssessmentResult::where('session_id', $latestSession->id)
    ->whereHas('standard', fn($q) => $q->where('code', 'like', 'A.%'))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "Query E (Latest session, Annex A controls only, maturity < 5): " . $annexGaps . "\n";

$clauseGaps = AssessmentResult::where('session_id', $latestSession->id)
    ->whereHas('standard', fn($q) => $q->where('type', 'clausa'))
    ->where('is_applicable', true)
    ->where('status', 'completed')
    ->where('maturity_rating', '<', 5)
    ->count();
echo "Query E2 (Latest session, Main Clauses only, maturity < 5): " . $clauseGaps . "\n";

