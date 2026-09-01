<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Document;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Marina;
use App\Models\Pipeline;
use App\Models\Task;
use App\Models\User;
use App\Models\Yacht;
use App\Support\Roles;
use Illuminate\Database\Seeder;

/**
 * A believable working week for a demo or a walkthrough: a fleet at real
 * superyacht scale, clients across all four types, leads at every stage of the
 * response clock, and deals spread across the charter board.
 *
 * Local and staging only — never seeded into production.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $sales = User::where('email', 'sales@walidia.test')->first() ?? User::role(Roles::SALES)->first();
        $ops = User::where('email', 'operations@walidia.test')->first();
        $marinas = Marina::pluck('id', 'name');
        $sources = LeadSource::pluck('id')->all();

        /* ── Fleet ─────────────────────────────────────────────────────── */
        $fleet = Yacht::factory()
            ->count(8)
            ->sequence(
                ['is_charter_fleet' => true, 'is_for_sale' => false],
                ['is_charter_fleet' => true, 'is_for_sale' => true],
                ['is_charter_fleet' => false, 'is_for_sale' => true],
                ['is_charter_fleet' => true, 'is_managed' => true],
            )
            ->create([
                'home_marina_id' => $marinas['Yas Marina'] ?? null,
            ]);

        foreach ($fleet as $index => $yacht) {
            $yacht->update([
                'home_marina_id' => $marinas->values()->get($index % max($marinas->count(), 1)),
            ]);

            if ($yacht->is_charter_fleet) {
                $yacht->charterProfile()->create([
                    'hourly_rate' => 3500 + ($index * 500),
                    'half_day_rate' => 18000 + ($index * 2000),
                    'full_day_rate' => 32000 + ($index * 4000),
                    'overnight_rate' => 55000 + ($index * 5000),
                    'currency' => 'AED',
                    'min_hours' => 4,
                    'apa_percentage' => 25,
                    'is_bookable' => true,
                ]);
            }

            if ($yacht->is_for_sale) {
                $yacht->saleProfile()->create([
                    'asking_price' => 18_000_000 + ($index * 4_500_000),
                    'currency' => 'AED',
                    'price_visibility' => $index % 2 === 0 ? 'on_request' : 'public',
                ]);
            }

            if ($yacht->is_managed) {
                $yacht->managementProfile()->create([
                    'budget_annual' => 1_600_000,
                    'reporting_cadence' => 'monthly',
                    'technical_manager_id' => $ops?->id,
                ]);
            }
        }

        /* ── Companies and clients ─────────────────────────────────────── */
        $companies = Company::factory()->count(5)->create(['assigned_user_id' => $sales?->id]);

        $clients = Client::factory()
            ->count(18)
            ->state(fn (): array => [
                'assigned_user_id' => $sales?->id,
                'source_id' => $sources === [] ? null : fake()->randomElement($sources),
            ])
            ->create();

        // A handful of VIP records, so the field-level protection is visible
        // in a walkthrough rather than theoretical.
        Client::factory()->count(4)->vip()->verified()->create([
            'assigned_user_id' => $sales?->id,
            'company_id' => $companies->random()->id,
        ]);

        // Owners, with the yachts they own.
        $owners = Client::factory()->count(3)->owner()->verified()->create(['assigned_user_id' => $sales?->id]);

        foreach ($fleet->take(3) as $index => $yacht) {
            $yacht->owners()->attach($owners[$index % $owners->count()]->id, [
                'ownership_percentage' => 100,
                'is_primary' => true,
                'since' => now()->subYears(2),
            ]);
        }

        /* ── Leads at every point on the response clock ────────────────── */
        Lead::factory()->count(6)->create([
            'assigned_user_id' => null,
            'source_id' => $sources === [] ? null : fake()->randomElement($sources),
        ]);

        Lead::factory()->count(5)->contacted()->create([
            'assigned_user_id' => $sales?->id,
            'source_id' => $sources === [] ? null : fake()->randomElement($sources),
        ]);

        // Two leads that have already missed their SLA — the Follow-Up Pool
        // should never be empty in a demo, because it never is in real life.
        Lead::factory()->count(2)->create([
            'assigned_user_id' => $sales?->id,
            'sla_due_at' => now()->subHours(6),
            'first_response_at' => null,
        ]);

        /* ── Deals across the charter board ────────────────────────────── */
        $charter = Pipeline::with('stages')->where('key', 'charter')->first();

        if ($charter !== null) {
            foreach ($charter->stages->take(9) as $stage) {
                Deal::factory()->count(fake()->numberBetween(1, 3))->create([
                    'pipeline_id' => $charter->id,
                    'stage_id' => $stage->id,
                    'client_id' => $clients->random()->id,
                    'yacht_id' => $fleet->random()->id,
                    'assigned_user_id' => $sales?->id,
                    'stage_entered_at' => now()->subDays(fake()->numberBetween(0, 21)),
                ]);
            }
        }

        /* ── Tasks and a couple of documents ───────────────────────────── */
        Task::factory()->count(6)->create([
            'assigned_user_id' => $sales?->id,
            'subject_type' => 'client',
            'subject_id' => $clients->random()->id,
        ]);

        Task::factory()->count(2)->overdue()->create([
            'assigned_user_id' => $sales?->id,
        ]);

        Document::factory()->count(4)->expiring()->create([
            'subject_type' => 'yacht',
            'subject_id' => $fleet->random()->id,
            'category' => 'certificate',
            'uploaded_by' => $ops?->id,
        ]);

        $this->command->info(sprintf(
            'Seeded %d yachts, %d clients, %d leads and a populated charter board.',
            $fleet->count(),
            Client::withoutOwnerScope()->count(),
            Lead::withoutOwnerScope()->count(),
        ));
    }
}
