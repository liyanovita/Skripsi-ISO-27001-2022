<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;

$session = AssessmentSession::orderByDesc('updated_at')->first();
$gapResults = $session->results
    ->filter(fn($r) => $r->is_applicable && $r->status === 'completed' && (is_null($r->maturity_rating) || $r->maturity_rating < 5) && $r->standard)
    ->sortBy('standard.code')
    ->values();

echo "Gap Results count: " . $gapResults->count() . "\n";
foreach ($gapResults->take(5) as $r) {
    echo " - Code: {$r->standard->code}, Maturity: " . var_export($r->maturity_rating, true) . ", Gap: {$r->gap}\n";
    echo "   AI Rec: " . substr($r->ai_recommendation ?? 'NULL', 0, 80) . "\n";
    echo "   Action Plan: " . json_encode($r->corrective_action_plan) . "\n";
}
