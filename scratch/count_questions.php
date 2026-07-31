<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\IsoStandard;

$all = IsoStandard::all();
$withQuestions = $all->filter(fn($s) => is_array($s->questions) && count($s->questions) > 0);
$annex = $withQuestions->filter(fn($s) => !in_array($s->type, ['clause', 'clausa']));
$clauses = $withQuestions->filter(fn($s) => in_array($s->type, ['clause', 'clausa']));

echo "TOTAL_WITH_QUESTIONS: " . $withQuestions->count() . "\n";
echo "ANNEX_WITH_QUESTIONS: " . $annex->count() . "\n";
echo "CLAUSES_WITH_QUESTIONS: " . $clauses->count() . "\n";
