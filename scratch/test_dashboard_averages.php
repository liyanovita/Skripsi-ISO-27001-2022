<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@example.com')->first() ?? \App\Models\User::where('name', 'LIKE', '%Liya%')->first();
$userId = $user->id;

// Get all completed sessions
$completedSessions = \App\Models\AssessmentSession::where(function($q) use ($userId) {
        $q->where('user_id', $userId)
          ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
    })
    ->where('status', 'completed')
    ->get();

echo "Completed Sessions Count: " . $completedSessions->count() . PHP_EOL;

$sessionGapsCounts = [];
$sessionHighRiskCounts = [];
$sessionMedRiskCounts = [];
$sessionLowRiskCounts = [];

foreach ($completedSessions as $sess) {
    $results = \App\Models\AssessmentResult::with('standard')
        ->where('session_id', $sess->id)
        ->where('status', 'completed')
        ->get()
        ->filter(fn($r) => $r->standard && is_array($r->standard->questions) && count($r->standard->questions) > 0);

    $gaps = $results->where('is_applicable', true)->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5);
    $gapsCount = $gaps->count();

    $highGaps = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'High' || ($r->risk_priority === null && $r->maturity_rating <= 2))->count();
    $medGaps = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'Medium' || ($r->risk_priority === null && $r->maturity_rating == 3))->count();
    $lowGaps = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'Low' || ($r->risk_priority === null && $r->maturity_rating == 4))->count();

    $sessionGapsCounts[] = $gapsCount;
    $sessionHighRiskCounts[] = $highGaps;
    $sessionMedRiskCounts[] = $medGaps;
    $sessionLowRiskCounts[] = $lowGaps;

    echo "Session ID {$sess->id} ({$sess->name}): Score = {$sess->overall_maturity_score}, Gaps = {$gapsCount} (High: {$highGaps}, Med: {$medGaps}, Low: {$lowGaps})" . PHP_EOL;
}

$avgGaps = count($sessionGapsCounts) > 0 ? (int) round(array_sum($sessionGapsCounts) / count($sessionGapsCounts)) : 0;
$avgHigh = count($sessionHighRiskCounts) > 0 ? (int) round(array_sum($sessionHighRiskCounts) / count($sessionHighRiskCounts)) : 0;
$avgMed = count($sessionMedRiskCounts) > 0 ? (int) round(array_sum($sessionMedRiskCounts) / count($sessionMedRiskCounts)) : 0;
$avgLow = count($sessionLowRiskCounts) > 0 ? (int) round(array_sum($sessionLowRiskCounts) / count($sessionLowRiskCounts)) : 0;

echo "--------------------------------------------------------" . PHP_EOL;
echo "AVERAGE Gaps Per Completed Audit Session: " . $avgGaps . PHP_EOL;
echo "AVERAGE High Risk Gaps  : " . $avgHigh . PHP_EOL;
echo "AVERAGE Medium Risk Gaps: " . $avgMed . PHP_EOL;
echo "AVERAGE Low Risk Gaps   : " . $avgLow . PHP_EOL;
