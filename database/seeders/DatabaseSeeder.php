<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always: the things the application cannot run without.
        $this->call([
            RolesAndPermissionsSeeder::class,
            PipelineSeeder::class,
            ReferenceDataSeeder::class,
            GateRuleSeeder::class,
            FinanceDefaultsSeeder::class,
        ]);

        // Never in production: demo accounts and sample records.
        if (app()->environment('local', 'testing')) {
            $this->call([
                DevUsersSeeder::class,
                DemoDataSeeder::class,
            ]);
        }
    }
}
