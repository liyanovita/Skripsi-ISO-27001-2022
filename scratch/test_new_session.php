<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssessmentSession;
use App\Models\User;
use App\Services\Assessment\SessionService;

$service = app(SessionService::class);
$user = User::first();

$session = $service->createSession([
    'user_id' => $user->id,
    'name' => 'Test Progress Session ' . time(),
    'status' => 'in_progress',
]);

echo "Created Session ID: " . $session->id . "\n";
echo "Total Results: " . $session->results()->count() . "\n";
echo "Results with status = 'completed': " . $session->results()->where('status', 'completed')->count() . "\n";
echo "Results with maturity_rating != null: " . $session->results()->whereNotNull('maturity_rating')->count() . "\n";
echo "Results with maturity_rating = 0: " . $session->results()->where('maturity_rating', 0)->count() . "\n";

// Check progress calculation using SessionService
$progress = $service->getAssessmentProgress($session);
echo "SessionService getAssessmentProgress: " . json_encode($progress) . "\n";

// Check progress calculation as done in Admin/SessionController:
$results = $session->results;
$assessable = $results->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
$applicable = $assessable->filter(fn($r) => $r->is_applicable);
$assessed = $applicable->filter(fn($r) => $r->maturity_rating !== null);
$completionPct = $applicable->count() > 0 ? round(($assessed->count() / $applicable->count()) * 100) : 0;
echo "Admin/SessionController completionPct: " . $completionPct . "%\n";

// Clean up test session
$session->delete();
