<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\IsoStandard;

echo "Total IsoStandard rows: " . IsoStandard::count() . "\n";
echo "Types: " . json_encode(IsoStandard::selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type')) . "\n";
echo "Annex A controls: " . IsoStandard::where('code', 'like', 'A.%')->count() . "\n";
echo "Clauses (4-10): " . IsoStandard::where('type', 'clausa')->count() . "\n";
