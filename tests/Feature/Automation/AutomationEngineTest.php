<?php

declare(strict_types=1);

use App\Domain\Automation\MessageDispatcher;
use App\Domain\Automation\WorkflowEngine;
use App\Domain\Charter\Actions\ConfirmBooking;
use App\Domain\Operations\Actions\RunCharterDay;
use App\Models\Booking;
use App\Models\ChecklistItem;
use App\Models\ChecklistTemplate;
use App\Models\Client;
use App\Models\ClientJourney;
use App\Models\Communication;
use App\Models\MessageTemplate;
use App\Models\Task;
use App\Models\WorkflowRule;
use App\Models\WorkflowRun;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\AutomationSeeder;
use Database\Seeders\ChecklistTemplateSeeder;

/*
|--------------------------------------------------------------------------
| The automation engine
|--------------------------------------------------------------------------
|
| Three properties matter more than the features: nothing sends twice, nothing
| sends silently, and nothing sends by accident. Each of the tests below is one
| of those.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(AutomationSeeder::class);
});

function bookingForAutomation(array $attributes = []): Booking
{
    return Booking::factory()->create(array_merge([
        'client_id' => Client::factory()->create(['email' => 'guest@example.test'])->id,
        'yacht_id' => Yacht::factory()->create(['name' => 'Lady Walidia'])->id,
        'status' => 'confirmed',
    ], $attributes));
}

/* ── Nothing sends twice ─────────────────────────────────────────────────── */

it('queues a rule once however many times the event fires', function (): void {
    $booking = bookingForAutomation();
    $engine = app(WorkflowEngine::class);

    $first = $engine->fire('booking.confirmed', $booking);
    $second = $engine->fire('booking.confirmed', $booking);
    $third = $engine->fire('booking.confirmed', $booking);

    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and($third)->toBe(0)
        ->and(WorkflowRun::where('subject_id', $booking->id)->count())->toBe(1);
});

/* ── Nothing sends silently ──────────────────────────────────────────────── */

it('records why it skipped rather than saying nothing', function (): void {
    $booking = bookingForAutomation();
    $engine = app(WorkflowEngine::class);

    $engine->fire('booking.confirmed', $booking);

    // The charter is cancelled between the queue and the send.
    $rule = WorkflowRule::where('key', 'charter.booking_confirmation')->first();
    $rule->forceFill(['conditions' => [
        ['field' => 'status', 'operator' => 'not_equals', 'value' => 'cancelled'],
    ]])->save();

    $booking->forceFill(['status' => 'cancelled'])->save();

    $run = WorkflowRun::where('subject_id', $booking->id)->first();
    $outcome = $engine->run($run->fresh());

    expect($outcome)->toBe('skipped')
        ->and($run->fresh()->skip_reason)->toBe('Conditions no longer hold.')
        ->and(Communication::count())->toBe(0);
});

it('records a message it could not address, rather than dropping it', function (): void {
    // A client with no email is a data problem, and the record should say so.
    $booking = bookingForAutomation([
        'client_id' => Client::factory()->create(['email' => null])->id,
    ]);

    app(WorkflowEngine::class)->fire('booking.confirmed', $booking);
    app(WorkflowEngine::class)->runDue();

    $communication = Communication::latest('id')->first();

    expect($communication)->not->toBeNull()
        ->and($communication->status)->toBe('failed')
        ->and($communication->failure_reason)->toContain('No email address');
});

/* ── Nothing sends by accident ───────────────────────────────────────────── */

it('does not send a rule that was switched off after it was queued', function (): void {
    $booking = bookingForAutomation();
    $engine = app(WorkflowEngine::class);

    $engine->fire('booking.confirmed', $booking);

    WorkflowRule::where('key', 'charter.booking_confirmation')->update(['is_active' => false]);

    $run = WorkflowRun::where('subject_id', $booking->id)->first();

    expect($engine->run($run->fresh()))->toBe('skipped')
        ->and($run->fresh()->skip_reason)->toBe('The rule is no longer active.');
});

it('waits until the moment arrives before sending', function (): void {
    $booking = bookingForAutomation();

    $rule = WorkflowRule::create([
        'key' => 'test.later',
        'name' => 'Something for tomorrow',
        'trigger_type' => 'event',
        'trigger_event' => 'test.event',
        'offset_hours' => 24,
        'action' => 'create_task',
        'action_params' => ['title' => 'Not yet'],
        'audience' => 'role',
        'is_active' => true,
    ]);

    app(WorkflowEngine::class)->fire('test.event', $booking);

    expect(app(WorkflowEngine::class)->runDue())->toBe(['sent' => 0, 'skipped' => 0, 'failed' => 0])
        ->and(Task::where('title', 'Not yet')->exists())->toBeFalse();

    // Move the clock forward and it goes.
    $this->travel(25)->hours();

    expect(app(WorkflowEngine::class)->runDue()['sent'])->toBe(1)
        ->and(Task::where('title', 'Not yet')->exists())->toBeTrue();
});

/* ── What it actually does ───────────────────────────────────────────────── */

it('sends a message with the merge fields filled in', function (): void {
    $booking = bookingForAutomation();

    app(WorkflowEngine::class)->fire('booking.confirmed', $booking);
    $tally = app(WorkflowEngine::class)->runDue();

    $communication = Communication::latest('id')->first();

    expect($tally['sent'])->toBe(1)
        ->and($communication->status)->toBe('sent')
        ->and($communication->to_address)->toBe('guest@example.test')
        ->and($communication->body)->toContain('Lady Walidia')
        // No unresolved placeholder reaches a client.
        ->and($communication->body)->not->toContain('{{');
});

it('never lets a template reach an attribute nobody offered it', function (): void {
    $template = MessageTemplate::create([
        'key' => 'test.leaky',
        'name' => 'Leaky',
        'channel' => 'email',
        'body_en' => 'Passport: {{passport_number}} and notes: {{vip_notes}}.',
        'category' => 'client',
        'is_active' => true,
    ]);

    $booking = bookingForAutomation();

    app(MessageDispatcher::class)->send($template, $booking);

    $communication = Communication::latest('id')->first();

    // Unknown fields are left visibly unresolved rather than silently filled
    // from the model — a broken merge must never become a data leak.
    expect($communication->body)->toContain('{{passport_number}}')
        ->and($communication->body)->not->toContain('P1234');
});

/* ── The post-charter journey ────────────────────────────────────────────── */

it('opens a journey when the charter finishes', function (): void {
    $this->seed(ChecklistTemplateSeeder::class);

    $operations = userWithRole(Roles::OPERATIONS);
    $booking = bookingForAutomation(['status' => 'in_progress']);

    app(RunCharterDay::class)->arrive($booking, $operations);

    $journey = ClientJourney::where('booking_id', $booking->id)->first();

    expect($booking->fresh()->status)->toBe('completed')
        ->and($journey)->not->toBeNull()
        ->and($journey->type)->toBe('post_charter')
        ->and($journey->status)->toBe('open');
});

it('walks a complaint from raised to resolved, and will not accept a shrug', function (): void {
    $manager = userWithRole(Roles::ADMIN);

    $journey = ClientJourney::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'type' => 'post_charter',
    ]);

    $this->actingAs($manager)
        ->post("/crm/journeys/{$journey->id}/complaint", ['complaint_detail' => 'Catering arrived late and cold.'])
        ->assertRedirect();

    expect($journey->fresh()->hasOpenComplaint())->toBeTrue();

    // Twenty characters is the floor: "sorted" is not a resolution.
    $this->actingAs($manager)
        ->post("/crm/journeys/{$journey->id}/complaint/resolve", ['complaint_resolution' => 'Sorted'])
        ->assertSessionHasErrors('complaint_resolution');

    $this->actingAs($manager)
        ->post("/crm/journeys/{$journey->id}/complaint/resolve", [
            'complaint_resolution' => 'Caterer replaced and the charter fee partially refunded.',
        ])
        ->assertRedirect();

    expect($journey->fresh()->hasOpenComplaint())->toBeFalse()
        ->and($journey->fresh()->complaint_resolved_at)->not->toBeNull();
});

/* ── Checklists ──────────────────────────────────────────────────────────── */

it('stamps the checklist templates onto a booking when it is confirmed', function (): void {
    $this->seed(ChecklistTemplateSeeder::class);

    $sales = userWithRole(Roles::SALES);
    $booking = bookingForAutomation(['status' => 'deposit_pending']);

    app(ConfirmBooking::class)->confirm($booking, $sales);

    $booking->refresh();

    expect($booking->checklists()->count())->toBe(ChecklistTemplate::count())
        // The boarding gate reads this key by name; without it, nobody boards.
        ->and($booking->checklists()->first()->items()->count())->toBeGreaterThan(0);

    $safetyBriefing = ChecklistItem::query()
        ->whereHas('checklist', fn ($query) => $query->where('booking_id', $booking->id))
        ->where('key', 'safety_briefing')
        ->first();

    expect($safetyBriefing)->not->toBeNull()
        ->and($safetyBriefing->is_blocking)->toBeTrue();
});

/* ── The screens ─────────────────────────────────────────────────────────── */

it('serves the automation screens to an administrator', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    foreach ([
        '/engine/workflows' => 'Automation/Workflows/Index',
        '/engine/message-templates' => 'Automation/MessageTemplates/Index',
        '/engine/communications' => 'Automation/Communications/Index',
        '/crm/journeys' => 'Crm/Journeys/Index',
        '/crm/rewards' => 'Crm/Rewards/Index',
    ] as $url => $component) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});

it('keeps the communications ledger read-only', function (): void {
    $admin = userWithRole(Roles::ADMIN);
    $communication = Communication::factory()->create();

    // No create, edit, update or destroy route exists at all.
    $this->actingAs($admin)->get('/engine/communications/create')->assertNotFound();
    $this->actingAs($admin)->put("/engine/communications/{$communication->id}")->assertMethodNotAllowed();
    $this->actingAs($admin)->delete("/engine/communications/{$communication->id}")->assertMethodNotAllowed();
});
