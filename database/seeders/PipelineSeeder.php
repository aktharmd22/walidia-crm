<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LostReason;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

/**
 * The three pipelines from the brief, as data.
 *
 * Stage colour comes from the shared status palette rather than an arbitrary
 * hex, so a card's colour means the same thing as a pill's anywhere else.
 */
class PipelineSeeder extends Seeder
{
    public function run(): void
    {
        $pipelines = [
            'charter' => [
                'name' => 'Charter',
                'stages' => [
                    ['lead', 'Lead', 'neutral', 5],
                    ['qualified', 'Qualified', 'info', 15],
                    ['registered', 'Registered', 'info', 25],
                    ['proposal', 'Proposal', 'warning', 40],
                    ['negotiation', 'Negotiation', 'attention', 55],
                    ['booking', 'Booking', 'attention', 70],
                    ['contract_signed', 'Contract Signed', 'info', 80],
                    ['deposit_paid', 'Deposit Paid', 'info', 90],
                    ['confirmed', 'Confirmed', 'success', 95],
                    ['operations', 'Operations', 'success', 98],
                    ['completed', 'Completed', 'success', 100, true],
                    ['closed_lost', 'Closed Lost', 'danger', 0, false, true],
                ],
            ],
            'buyer' => [
                'name' => 'Buyer',
                'stages' => [
                    ['lead', 'Lead', 'neutral', 5],
                    ['qualified', 'Qualified', 'info', 10],
                    ['registered', 'Registered', 'info', 15],
                    ['matched', 'Matched', 'info', 25],
                    ['shortlisted', 'Shortlisted', 'warning', 35],
                    ['viewing', 'Viewing', 'warning', 45],
                    ['offer', 'Offer', 'attention', 60],
                    ['negotiation', 'Negotiation', 'attention', 70],
                    ['survey', 'Survey', 'warning', 80],
                    ['purchase_agreement', 'Purchase Agreement', 'info', 88],
                    ['paid', 'Paid', 'info', 95],
                    ['ownership_transfer', 'Ownership Transfer', 'success', 98],
                    ['closed_won', 'Closed Won', 'success', 100, true],
                    ['closed_lost', 'Closed Lost', 'danger', 0, false, true],
                ],
            ],
            'seller' => [
                'name' => 'Seller',
                'stages' => [
                    ['lead', 'Lead', 'neutral', 5],
                    ['qualified', 'Qualified', 'info', 15],
                    ['listed', 'Listed', 'info', 30],
                    ['active', 'Active', 'info', 40],
                    ['viewing', 'Viewing', 'warning', 50],
                    ['offer', 'Offer', 'attention', 65],
                    ['negotiation', 'Negotiation', 'attention', 75],
                    ['under_offer', 'Under Offer', 'warning', 85],
                    ['survey', 'Survey', 'warning', 90],
                    ['sold', 'Sold', 'success', 100, true],
                    ['closed_lost', 'Closed Lost', 'danger', 0, false, true],
                ],
            ],
        ];

        foreach ($pipelines as $key => $definition) {
            $pipeline = Pipeline::updateOrCreate(
                ['key' => $key],
                ['name' => $definition['name'], 'is_active' => true],
            );

            foreach ($definition['stages'] as $order => $stage) {
                [$stageKey, $name, $tone, $probability] = $stage;

                PipelineStage::updateOrCreate(
                    ['pipeline_id' => $pipeline->id, 'key' => $stageKey],
                    [
                        'name' => $name,
                        'sort_order' => $order,
                        'colour_token' => $tone,
                        'probability' => $probability,
                        'is_won' => $stage[4] ?? false,
                        'is_lost' => $stage[5] ?? false,
                    ],
                );
            }
        }

        // A lost deal without a reason cannot be reported on, so the reasons
        // exist from day one rather than being invented per user.
        foreach ([
            'Price too high',
            'Dates not available',
            'Chose a competitor',
            'Client went quiet',
            'Requirements changed',
            'Not qualified / not serious',
            'Vessel unsuitable',
            'Timing — will return later',
        ] as $order => $label) {
            LostReason::updateOrCreate(
                ['label' => $label, 'pipeline_id' => null],
                ['sort_order' => $order, 'is_active' => true],
            );
        }

        $this->command->info('Seeded 3 pipelines with stages and 8 lost reasons.');
    }
}
