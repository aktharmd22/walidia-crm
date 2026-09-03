<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Task;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\GateRuleSeeder;

/*
|--------------------------------------------------------------------------
| The dashboard
|--------------------------------------------------------------------------
|
| Every panel here runs a query, and a query written from memory rather than
| from the schema is how the front page of the application returned a 500 with
| `Unknown column 'leads.lead_source_id'`. Loading the page is the assertion:
| if any of these queries is wrong, this test is red before a browser is.
|
*/

beforeEach(function (): void {
    seedRoles();
});

it('loads with an empty database', function (): void {
    // The first thing a new deployment does is render this page against
    // nothing at all — no divide-by-zero, no null date, no missing relation.
    $this->actingAs(userWithRole(Roles::ADMIN))
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/MyDay')
            ->has('metrics', 4)
            ->has('revenue', 12)
            ->has('mix', 3)
            ->has('sources')
            ->has('charters')
            ->has('blockers')
            ->has('tasks')
            ->has('expiring'));
});

it('loads with real records behind every panel', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    $yacht = Yacht::factory()->create(['status' => 'active']);
    $client = Client::factory()->create();

    Booking::factory()->create([
        'client_id' => $client->id,
        'yacht_id' => $yacht->id,
        'status' => 'confirmed',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHours(8),
    ]);

    Payment::factory()->create([
        'client_id' => $client->id,
        'amount_aed' => 42000,
        'cleared_at' => now(),
    ]);

    // The join that broke: leads reach their source through `source_id`.
    Lead::factory()->count(3)->create([
        'source_id' => LeadSource::factory()->create(['name' => 'WhatsApp'])->id,
    ]);

    Certificate::factory()->create([
        'yacht_id' => $yacht->id,
        'expires_on' => now()->addDays(10)->toDateString(),
        'blocks_charter' => true,
    ]);

    Task::factory()->create(['assigned_user_id' => $admin->id, 'status' => 'open']);

    $this->actingAs($admin)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/MyDay')
            ->where('metrics.0.key', 'revenue')
            ->has('sources.0.name')
            ->has('sources.0.share')
            ->has('charters.0.yacht')
            ->has('expiring.0.title')
            ->has('tasks.0.title'));
});

it('narrows the revenue chart to the window asked for', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    foreach ([3, 6, 12] as $months) {
        $this->actingAs($admin)
            ->get("/?months={$months}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('months', $months)->has('revenue', $months));
    }

    // Anything else falls back to a year rather than reaching the query.
    $this->actingAs($admin)
        ->get('/?months=9999')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('months', 12)->has('revenue', 12));
});

it('serves the alerts and calendar screens', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    $this->actingAs($admin)->get('/dashboard/alerts')->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard/Alerts')->has('hard')->has('expiring'));

    $this->actingAs($admin)->get('/dashboard/calendar')->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard/Calendar')->has('events')->has('month'));
});

it('names a blocked charter and says what is missing', function (): void {
    $this->seed(GateRuleSeeder::class);

    $admin = userWithRole(Roles::ADMIN);

    // Confirmed, sailing soon, and no cleared deposit behind it.
    Booking::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'yacht_id' => Yacht::factory()->create()->id,
        'status' => 'confirmed',
        'starts_at' => now()->addDays(3),
        'operational_release_at' => null,
    ]);

    $this->actingAs($admin)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('blockers.0.reasons.0')
            // The gate's own wording, so the dashboard and the record agree.
            ->where('blockers.0.reasons.0', fn (string $reason) => $reason !== ''));
});
