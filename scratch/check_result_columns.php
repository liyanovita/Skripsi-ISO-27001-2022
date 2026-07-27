<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentResult;

$result = AssessmentResult::first();
echo "AssessmentResult attributes:\n";
print_r(array_keys($result->getAttributes()));

if ($result) {
    echo "\nSample Result:\n";
    echo "code: " . $result->standard?->code . "\n";
    echo "corrective_action_plan: " . json_encode($result->corrective_action_plan) . "\n";
    echo "ai_guidance: " . json_encode($result->ai_guidance ?? null) . "\n";
}
