<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\IsoStandard;

class IsoStandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key constraints to allow truncating tables with self-references
        Schema::disableForeignKeyConstraints();
        IsoStandard::truncate();
        Schema::enableForeignKeyConstraints();

        $this->call([
            IsoClauseSeeder::class,
            IsoAnnexA5Seeder::class,
            IsoAnnexA6Seeder::class,
            IsoAnnexA7Seeder::class,
            IsoAnnexA8Seeder::class,
        ]);
    }
}
