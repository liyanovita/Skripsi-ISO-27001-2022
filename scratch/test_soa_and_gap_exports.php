<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AssessmentSession;
use App\Exports\SoaExport;
use App\Exports\AssessmentReportExport;
use Maatwebsite\Excel\Facades\Excel;

$session = AssessmentSession::orderByDesc('updated_at')->first();
echo "Testing Exports for Session ID: " . $session->id . " (" . $session->name . ")\n";

try {
    $soaExport = new SoaExport($session->id);
    echo "SoaExport instantiated successfully. Sheets count: " . count($soaExport->sheets()) . "\n";
} catch (\Throwable $e) {
    echo "SoaExport Error: " . $e->getMessage() . "\n";
}

try {
    $gapExport = new AssessmentReportExport($session->id);
    $collection = $gapExport->collection();
    echo "AssessmentReportExport instantiated successfully. Gap rows count: " . $collection->count() . "\n";
} catch (\Throwable $e) {
    echo "AssessmentReportExport Error: " . $e->getMessage() . "\n";
}
