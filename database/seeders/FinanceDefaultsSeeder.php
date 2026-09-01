<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use App\Models\VatRate;
use Illuminate\Database\Seeder;

/**
 * Tax treatments and cancellation tiers.
 *
 * Both are placeholders until the client's tax advisor confirms Q5 and the
 * business confirms Q10 — which is exactly why they are rows rather than
 * constants in a calculation.
 */
class FinanceDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['standard', 'Standard rated (5%)', 5.00, 'standard', true],
            ['zero', 'Zero rated (0%)', 0.00, 'zero_rated', false],
            ['out_of_scope', 'Outside the scope of UAE VAT', 0.00, 'out_of_scope', false],
            ['reverse_charge', 'Reverse charge', 0.00, 'reverse_charge', false],
        ];

        foreach ($rates as [$code, $label, $rate, $treatment, $default]) {
            VatRate::updateOrCreate(
                ['code' => $code],
                [
                    'label' => $label,
                    'rate_pct' => $rate,
                    'treatment' => $treatment,
                    'is_default' => $default,
                    'effective_from' => '2018-01-01',
                ],
            );
        }

        CancellationPolicy::updateOrCreate(
            ['name' => 'Standard charter policy'],
            [
                'applies_to' => 'charter',
                'is_default' => true,
                // Placeholder tiers pending Q10.
                'rules' => [
                    ['days_before' => 30, 'fee_pct' => 0],
                    ['days_before' => 7, 'fee_pct' => 50],
                    ['days_before' => 0, 'fee_pct' => 100],
                ],
            ],
        );

        $this->command->info('Seeded 4 VAT treatments and the default cancellation policy.');
    }
}
