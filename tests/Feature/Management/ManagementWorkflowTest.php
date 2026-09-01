<?php

declare(strict_types=1);

use App\Domain\Gates\GateEvaluator;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\MaintenanceJob;
use App\Models\ManagementAgreement;
use App\Models\OwnerStatement;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\GateRuleSeeder;

/*
|--------------------------------------------------------------------------
| Running someone else's yacht
|--------------------------------------------------------------------------
|
| The certificate register is the load-bearing table: a charter whose safety
| certificate has lapsed does not sail, and the gate reads this to know.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(GateRuleSeeder::class);
});

it('will not start a charter on a yacht whose certificate has lapsed', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $yacht = Yacht::factory()->create();

    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'name' => 'Safety Equipment Certificate',
        'blocks_charter' => true,
        'expires_on' => now()->subWeek()->toDateString(),
    ]);

    $booking = Booking::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'yacht_id' => $yacht->id,
        'status' => 'confirmed',
    ]);

    $gate = app(GateEvaluator::class)->forTransition($booking, 'status', 'in_progress', $operations);

    // The failure names the certificate, not "compliance".
    expect($gate->verdict)->toBe('block')
        ->and($gate->failures[0]->message)->toContain('Safety Equipment Certificate');
});

it('lets the charter start once every blocking certificate is valid', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $yacht = Yacht::factory()->create();

    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'blocks_charter' => true,
        'expires_on' => now()->addYear()->toDateString(),
    ]);

    // An expired certificate that does not block charter is paperwork, not a gate.
    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'name' => 'Tonnage Certificate',
        'blocks_charter' => false,
        'expires_on' => now()->subMonth()->toDateString(),
    ]);

    $booking = Booking::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'yacht_id' => $yacht->id,
        'status' => 'confirmed',
    ]);

    expect(app(GateEvaluator::class)->forTransition($booking, 'status', 'in_progress', $operations)->verdict)
        ->toBe('pass');
});

it('shows the fleet compliance board with expired certificates first', function (): void {
    $manager = userWithRole(Roles::ADMIN);

    $yacht = Yacht::factory()->create(['name' => 'Lady Walidia']);

    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'name' => 'Radio Licence',
        'expires_on' => now()->subDays(3)->toDateString(),
    ]);

    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'name' => 'Registry Certificate',
        'expires_on' => now()->addYears(3)->toDateString(),
    ]);

    $this->actingAs($manager)
        ->get('/management/certificates/expiry?days=90')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Management/Certificates/Expiry')
            ->where('days', 90)
            ->has('rows', 1)
            ->where('rows.0.name', 'Radio Licence')
            ->where('rows.0.is_expired', true));
});

it('derives what the owner is owed rather than trusting a typed total', function (): void {
    $agreement = ManagementAgreement::factory()->create(['yacht_id' => Yacht::factory()->create()->id]);

    $statement = OwnerStatement::factory()->create([
        'management_agreement_id' => $agreement->id,
        'yacht_id' => $agreement->yacht_id,
        'charter_revenue' => 320000,
        'management_fee' => 45000,
        'operating_costs' => 60000,
        'maintenance_costs' => 25000,
        'crew_costs' => 80000,
        'net_to_owner' => 999999,
    ]);

    $statement->recalculate();

    expect((float) $statement->net_to_owner)->toBe(110000.0);
});

it('issues a statement deliberately, and only once', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $agreement = ManagementAgreement::factory()->create(['yacht_id' => Yacht::factory()->create()->id]);

    $statement = OwnerStatement::factory()->create([
        'management_agreement_id' => $agreement->id,
        'yacht_id' => $agreement->yacht_id,
        'status' => 'draft',
    ]);

    $this->actingAs($finance)
        ->post("/management/owner-statements/{$statement->id}/issue")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $statement->refresh();

    expect($statement->status)->toBe('issued')
        ->and($statement->issued_at)->not->toBeNull()
        ->and((float) $statement->net_to_owner)->toBe(110000.0);

    // A statement already with the owner cannot be issued a second time.
    $this->actingAs($finance)
        ->post("/management/owner-statements/{$statement->id}/issue")
        ->assertForbidden();
});

it('closes a maintenance job with what it actually cost', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $job = MaintenanceJob::factory()->create([
        'yacht_id' => Yacht::factory()->create()->id,
        'estimated_cost' => 12000,
        'status' => 'in_progress',
    ]);

    $this->actingAs($operations)
        ->post("/management/maintenance/{$job->id}/complete", [
            'actual_cost' => 14350,
            'notes' => 'Impeller replaced; parts sourced locally.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $job->refresh();

    expect($job->status)->toBe('done')
        ->and($job->completed_at)->not->toBeNull()
        ->and((float) $job->actual_cost)->toBe(14350.0);
});

it('serves the management screens', function (): void {
    $manager = userWithRole(Roles::ADMIN);

    foreach ([
        '/management/agreements' => 'Management/Agreements/Index',
        '/management/certificates' => 'Management/Certificates/Index',
        '/management/maintenance' => 'Management/Maintenance/Index',
        '/management/owner-statements' => 'Management/OwnerStatements/Index',
    ] as $url => $component) {
        $this->actingAs($manager)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});
