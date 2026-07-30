<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    $userId = $user->id;

    $rawResults = \App\Models\AssessmentResult::with(['standard', 'session'])
        ->whereHas('session', function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
        })
        ->where('status', 'completed')
        ->orderByDesc('updated_at')
        ->get()
        ->unique('iso_standard_id')
        ->values();

    $assessable = $rawResults->filter(fn($r) => $r->standard && is_array($r->standard->questions) && count($r->standard->questions) > 0);

    $totalAssessable = $assessable->count();
    $applicable = $assessable->where('is_applicable', true);
    $totalApplicable = $applicable->count();
    $excluded = $assessable->where('is_applicable', false)->count();

    $level5Count = $applicable->where('maturity_rating', '>=', 5)->count();
    $gapsCount = $applicable->where('maturity_rating', '<', 5)->count();

    if ($totalAssessable > 0) {
        echo "=== USER: {$user->name} (ID: {$user->id}) ===" . PHP_EOL;
        echo "Total Controls Evaluated : " . $totalAssessable . PHP_EOL;
        echo "Applicable Controls      : " . $totalApplicable . PHP_EOL;
        echo "Excluded Controls (N/A)  : " . $excluded . PHP_EOL;
        echo "Compliant (Score = 5)    : " . $level5Count . PHP_EOL;
        echo "Total Gaps (Score < 5)   : " . $gapsCount . PHP_EOL . PHP_EOL;
    }
}
exit;
