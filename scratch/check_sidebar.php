<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Models\User;

$user = User::where('role', 'user')->first() ?? User::first();
$session = AssessmentSession::where('user_id', $user->id)->orderByDesc('updated_at')->first();

if ($session) {
    echo "Session ID: " . $session->id . " (" . $session->name . ")\n";
    $results = $session->results()->with('standard')->get()->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
    
    $totalAll = $results->count();
    $applicable = $results->where('is_applicable', true)->count();
    $excluded = $results->where('is_applicable', false)->count();
    $completedApplicable = $results->where('is_applicable', true)->where('status', 'completed')->count();
    $compliant5 = $results->where('is_applicable', true)->where('status', 'completed')->where('maturity_rating', 5)->count();
    $compliant4Plus = $results->where('is_applicable', true)->where('status', 'completed')->where('maturity_rating', '>=', 4)->count();
    
    echo "Total Assessable Controls: " . $totalAll . "\n";
    echo "Applicable Controls: " . $applicable . "\n";
    echo "Excluded Controls: " . $excluded . "\n";
    echo "Completed Applicable Controls: " . $completedApplicable . "\n";
    echo "Compliant Controls (Maturity >= 4): " . $compliant4Plus . "\n";
    echo "Compliant Controls (Maturity == 5): " . $compliant5 . "\n";
}
