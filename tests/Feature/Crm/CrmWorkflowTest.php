<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Task;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\PipelineSeeder;

beforeEach(function (): void {
    seedRoles();
});

/* ── Clients ─────────────────────────────────────────────────────────── */

it('creates a client, references it and logs the creation on the timeline', function (): void {
    $sales = userWithRole(Roles::SALES);

    $this->actingAs($sales)->post('/clients', [
        'first_name' => 'Khalid',
        'last_name' => 'Al Mansouri',
        'client_type' => ['charter_guest', 'buyer'],
        'preferred_channel' => 'whatsapp',
        'vip_level' => 'none',
        'status' => 'active',
        'mobile' => '+971 50 123 4567',
    ])->assertRedirect();

    $client = Client::withoutOwnerScope()->latest('id')->first();

    expect($client->full_name)->toBe('Khalid Al Mansouri')
        ->and($client->reference)->toStartWith('CL-')
        ->and($client->client_type)->toBe(['charter_guest', 'buyer'])
        ->and($client->assigned_user_id)->toBe($sales->id)
        ->and($client->activities()->count())->toBe(1);
});

it('warns instead of creating a second client on the same mobile number', function (): void {
    $sales = userWithRole(Roles::SALES);

    Client::factory()->create(['assigned_user_id' => $sales->id, 'mobile' => '+971501234567']);

    $this->actingAs($sales)->post('/clients', [
        'first_name' => 'Same',
        'last_name' => 'Person',
        'client_type' => ['charter_guest'],
        'preferred_channel' => 'whatsapp',
        'vip_level' => 'none',
        'status' => 'active',
        'mobile' => '+971 50 123 4567',
    ])->assertSessionHasErrors('duplicate');

    expect(Client::withoutOwnerScope()->count())->toBe(1);
});

it('creates the duplicate anyway when the user confirms', function (): void {
    $sales = userWithRole(Roles::SALES);
    Client::factory()->create(['assigned_user_id' => $sales->id, 'mobile' => '+971501234567']);

    $this->actingAs($sales)->post('/clients', [
        'first_name' => 'Same',
        'last_name' => 'Person',
        'client_type' => ['charter_guest'],
        'preferred_channel' => 'whatsapp',
        'vip_level' => 'none',
        'status' => 'active',
        'mobile' => '+971 50 123 4567',
        'force' => true,
    ])->assertRedirect();

    expect(Client::withoutOwnerScope()->count())->toBe(2);
});

it('archives rather than deletes, and restores', function (): void {
    $sales = userWithRole(Roles::SALES);
    $client = Client::factory()->create(['assigned_user_id' => $sales->id]);

    $this->actingAs($sales)->delete("/clients/{$client->id}")->assertRedirect();

    expect(Client::withoutOwnerScope()->count())->toBe(0)
        ->and(Client::withTrashed()->withoutOwnerScope()->count())->toBe(1);

    $this->actingAs($sales)->post("/clients/{$client->id}/restore")->assertRedirect();

    expect(Client::withoutOwnerScope()->count())->toBe(1);
});

/* ── Leads ───────────────────────────────────────────────────────────── */

it('starts the response clock when a lead is created', function (): void {
    $sales = userWithRole(Roles::SALES);

    $this->actingAs($sales)->post('/leads', [
        'name' => 'Website enquiry',
        'business_line' => 'charter',
        'mobile' => '+971501112233',
    ])->assertRedirect();

    $lead = Lead::withoutOwnerScope()->latest('id')->first();

    expect($lead->sla_due_at)->not->toBeNull()
        ->and($lead->first_response_at)->toBeNull()
        ->and($lead->status)->toBe('new');
});

it('stops the response clock when contact is logged', function (): void {
    $sales = userWithRole(Roles::SALES);
    $lead = Lead::factory()->create(['assigned_user_id' => $sales->id]);

    $this->actingAs($sales)->post("/leads/{$lead->id}/log-contact", [
        'channel' => 'whatsapp',
        'summary' => 'Sent availability for 20 March',
    ])->assertRedirect();

    $lead->refresh();

    expect($lead->first_response_at)->not->toBeNull()
        ->and($lead->status)->toBe('contacted')
        ->and($lead->activities()->count())->toBe(1);
});

it('converts a lead into a client and opens a deal, keeping the lead intact', function (): void {
    $this->seed(PipelineSeeder::class);

    $sales = userWithRole(Roles::SALES);
    $lead = Lead::factory()->create([
        'assigned_user_id' => $sales->id,
        'business_line' => 'charter',
        'name' => 'Omar Al Zaabi',
        'mobile' => '+971505556677',
    ]);

    $this->actingAs($sales)->post("/leads/{$lead->id}/convert", ['create_deal' => true])->assertRedirect();

    $lead->refresh();
    $client = Client::withoutOwnerScope()->latest('id')->first();
    $deal = Deal::withoutOwnerScope()->latest('id')->first();

    expect($lead->status)->toBe('registered')
        ->and($lead->converted_at)->not->toBeNull()
        ->and($lead->client_id)->toBe($client->id)
        ->and($client->full_name)->toBe('Omar Al Zaabi')
        ->and($deal->client_id)->toBe($client->id)
        ->and($deal->pipeline->key)->toBe('charter');
});

it('reuses an existing client when converting a lead that matches one', function (): void {
    $this->seed(PipelineSeeder::class);

    $sales = userWithRole(Roles::SALES);
    $existing = Client::factory()->create(['assigned_user_id' => $sales->id, 'mobile' => '+971509998877']);
    $lead = Lead::factory()->create(['assigned_user_id' => $sales->id, 'mobile' => '+971509998877']);

    $this->actingAs($sales)->post("/leads/{$lead->id}/convert")->assertRedirect();

    expect(Client::withoutOwnerScope()->count())->toBe(1)
        ->and($lead->fresh()->client_id)->toBe($existing->id);
});

/* ── Deals ───────────────────────────────────────────────────────────── */

it('records where a deal moved from, and refuses a lost move without a reason', function (): void {
    $this->seed(PipelineSeeder::class);

    $sales = userWithRole(Roles::SALES);
    $pipeline = Pipeline::with('stages')->where('key', 'charter')->first();
    $first = $pipeline->stages->first();
    $proposal = $pipeline->stages->firstWhere('key', 'proposal');
    $lost = $pipeline->stages->firstWhere('is_lost', true);

    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id,
        'stage_id' => $first->id,
        'assigned_user_id' => $sales->id,
    ]);

    $this->actingAs($sales)->post("/deals/{$deal->id}/stage", ['stage_id' => $proposal->id])->assertRedirect();

    expect($deal->fresh()->stage_id)->toBe($proposal->id)
        ->and($deal->activities()->latest('id')->first()->summary)->toContain('Proposal');

    $this->actingAs($sales)
        ->post("/deals/{$deal->id}/stage", ['stage_id' => $lost->id])
        ->assertSessionHasErrors('lost_reason_id');

    expect($deal->fresh()->stage_id)->toBe($proposal->id);
});

/* ── Fleet ───────────────────────────────────────────────────────────── */

it('refuses a cruising capacity above the static capacity', function (): void {
    $ops = userWithRole(Roles::OPERATIONS);

    $this->actingAs($ops)->post('/fleet/yachts', [
        'name' => 'Test Hull',
        'status' => 'active',
        'capacity_static' => 40,
        'capacity_cruising' => 60,
    ])->assertSessionHasErrors('capacity_cruising');

    expect(Yacht::count())->toBe(0);
});

it('creates the commercial profiles a yacht is flagged for', function (): void {
    $ops = userWithRole(Roles::OPERATIONS);

    $this->actingAs($ops)->post('/fleet/yachts', [
        'name' => 'Serenity IX',
        'status' => 'active',
        'is_charter_fleet' => true,
        'is_for_sale' => true,
        'is_managed' => false,
    ])->assertRedirect();

    $yacht = Yacht::latest('id')->first();

    expect($yacht->charterProfile)->not->toBeNull()
        ->and($yacht->saleProfile)->not->toBeNull()
        ->and($yacht->managementProfile)->toBeNull()
        ->and($yacht->reference)->toStartWith('YT-');
});

it('answers availability from the one table that owns fleet occupancy', function (): void {
    $yacht = Yacht::factory()->create();

    $yacht->availabilityBlocks()->create([
        'starts_at' => now()->addDays(5),
        'ends_at' => now()->addDays(7),
        'type' => 'booking',
    ]);

    expect($yacht->isAvailableBetween(now()->addDays(6), now()->addDays(8)))->toBeFalse()
        ->and($yacht->isAvailableBetween(now()->addDays(8), now()->addDays(9)))->toBeTrue();
});

it('ignores a lapsed option hold when answering availability', function (): void {
    $yacht = Yacht::factory()->create();

    $yacht->availabilityBlocks()->create([
        'starts_at' => now()->addDays(5),
        'ends_at' => now()->addDays(7),
        'type' => 'option_hold',
        'expires_at' => now()->subHour(),
    ]);

    expect($yacht->isAvailableBetween(now()->addDays(5), now()->addDays(7)))->toBeTrue();
});

/* ── Tasks ───────────────────────────────────────────────────────────── */

it('completes a task and stamps who did it', function (): void {
    $sales = userWithRole(Roles::SALES);
    $task = Task::factory()->create(['assigned_user_id' => $sales->id]);

    $this->actingAs($sales)->post("/tasks/{$task->id}/complete")->assertRedirect();

    $task->refresh();

    expect($task->status)->toBe('done')
        ->and($task->completed_by)->toBe($sales->id)
        ->and($task->completed_at)->not->toBeNull();
});

it('shows a user their own tasks by default', function (): void {
    $mine = userWithRole(Roles::SALES);
    $other = userWithRole(Roles::SALES);

    Task::factory()->count(2)->create(['assigned_user_id' => $mine->id]);
    Task::factory()->count(3)->create(['assigned_user_id' => $other->id]);

    $this->actingAs($mine)
        ->get('/tasks')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('rows.data', 2));
});
