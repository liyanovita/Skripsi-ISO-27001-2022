<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%Liya%')->first() ?? \App\Models\User::first();
$userId = $user->id;

$completedSessions = \App\Models\AssessmentSession::where(function($q) use ($userId) {
        $q->where('user_id', $userId)
          ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
    })
    ->where('status', 'completed')
    ->get();

echo "Completed Sessions: " . $completedSessions->count() . PHP_EOL;

$domainScores = [
    'Clauses (4-10)' => [],
    'A.5 Org Controls' => [],
    'A.6 People Controls' => [],
    'A.7 Physical Controls' => [],
    'A.8 Tech Controls' => [],
];

$distCompliant = [];
$distPartial = [];
$distNonCompliant = [];

$checkFns = [
    'Clauses (4-10)' => fn($code) => str_starts_with($code, '4.') || str_starts_with($code, '5.') || str_starts_with($code, '6.') || str_starts_with($code, '7.') || str_starts_with($code, '8.') || str_starts_with($code, '9.') || str_starts_with($code, '10.'),
    'A.5 Org Controls' => fn($code) => str_starts_with($code, 'A.5'),
    'A.6 People Controls' => fn($code) => str_starts_with($code, 'A.6'),
    'A.7 Physical Controls' => fn($code) => str_starts_with($code, 'A.7'),
    'A.8 Tech Controls' => fn($code) => str_starts_with($code, 'A.8'),
];

foreach ($completedSessions as $s) {
    $results = \App\Models\AssessmentResult::with('standard')
        ->where('session_id', $s->id)
        ->get()
        ->filter(fn($r) => $r->standard && is_array($r->standard->questions) && count($r->standard->questions) > 0);

    $appResults = $results->where('is_applicable', true);
    $distCompliant[] = $appResults->where('maturity_rating', '>=', 4)->count();
    $distPartial[] = $appResults->whereBetween('maturity_rating', [2, 3])->count();
    $distNonCompliant[] = $appResults->where('maturity_rating', '<', 2)->count();

    foreach ($checkFns as $dName => $fn) {
        $group = $appResults->filter(fn($r) => $fn($r->standard->code ?? ''));
        $avg = $group->count() > 0 ? $group->avg('maturity_rating') : 0;
        $domainScores[$dName][] = $avg;
    }
}

echo "=== AVERAGE COMPLIANCE DISTRIBUTION (Persebaran Maturity) ===" . PHP_EOL;
echo "Avg Compliant (Score >= 4)      : " . round(array_sum($distCompliant) / count($distCompliant)) . PHP_EOL;
echo "Avg Partial (Score 2-3)         : " . round(array_sum($distPartial) / count($distPartial)) . PHP_EOL;
echo "Avg Non-Compliant (Score 0-1)   : " . round(array_sum($distNonCompliant) / count($distNonCompliant)) . PHP_EOL;

echo "=== AVERAGE DOMAIN RADAR SCORES ===" . PHP_EOL;
foreach ($domainScores as $dName => $scores) {
    echo "Domain '$dName': Avg = " . number_format(array_sum($scores) / count($scores), 2) . PHP_EOL;
}
