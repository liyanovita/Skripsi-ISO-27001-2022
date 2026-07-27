<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditTrail;
use App\Models\User;
use App\Services\ExcelExportService;

$user = User::where('role', 'user')->first() ?? User::first();
echo "Testing Audit Trail Excel Export for User: " . $user->name . " (ID: " . $user->id . ")\n";

$query = AuditTrail::with(['user', 'model' => function ($morphTo) {
    $morphTo->morphWith([\App\Models\AssessmentResult::class => ['standard']]);
}])->where('user_id', $user->id)->orderByDesc('created_at');

$trails = $query->limit(10)->get();

echo "Found " . $trails->count() . " audit trail rows.\n";

$columns = ['Date & Time', 'User', 'Control Code', 'Field Changed', 'Old Value', 'New Value'];
$booleanFields = ['is_applicable'];
$rows = [];

foreach ($trails as $t) {
    $isBool = in_array($t->field_changed, $booleanFields);
    $oldDisplay = !is_null($t->old_value) ? ($isBool ? ($t->old_value == '1' ? 'Yes' : 'No') : $t->old_value) : '-';
    $newDisplay = !is_null($t->new_value) ? ($isBool ? ($t->new_value == '1' ? 'Yes' : 'No') : $t->new_value) : '-';

    $rows[] = [
        $t->created_at->format('Y-m-d H:i:s'),
        $t->user->name ?? 'System',
        $t->model?->standard?->code ?? 'N/A',
        $t->field_changed,
        $oldDisplay,
        $newDisplay,
    ];
}

if (count($rows) > 0) {
    echo "First row preview:\n";
    print_r($rows[0]);
}

$binary = ExcelExportService::createXlsx($columns, $rows, 'Audit Trail');
echo "Generated Excel filesize: " . strlen($binary) . " bytes.\n";
