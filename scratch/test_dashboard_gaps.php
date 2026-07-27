<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\Intelligence\DashboardService;

$user = User::where('role', 'user')->first() ?? User::first();
$service = app(DashboardService::class);
$data = $service->getDashboardData($user->id);

echo "Dashboard User ID: " . $user->id . "\n";
echo "Total Gaps on Dashboard: " . ($data['totalGaps'] ?? 'N/A') . "\n";
echo "High Risk Gaps on Dashboard: " . ($data['highRiskGapsCount'] ?? 'N/A') . "\n";
