<?php

declare(strict_types=1);

use App\Domain\Gates\GateEvaluator;
use App\Domain\Operations\Actions\DispatchCrew;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\ChecklistItem;
use App\Models\Client;
use App\Models\Crew;
use App\Models\CrewAssignment;
use App\Models\CrewDocument;
use App\Models\DamageReport;
use App\Models\GateOverride;
use App\Models\OperationsChecklist;
use App\Models\SecurityDeposit;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\GateRuleSeeder;

/*
|--------------------------------------------------------------------------
| The charter day, and the three gates around it
|--------------------------------------------------------------------------
|
| Crew are not dispatched before Finance releases the booking or with an
| expired document; guests do not board before their IDs are verified and the
| safety briefing is logged; and the client's deposit is not released while a
| damage inspection is still open.
|
| Each of these has cost a charter business real money somewhere. They are
| tested as behaviour, not as UI state.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(GateRuleSeeder::class);
});

/**
 * A confirmed charter with a yacht and a client, ready for the day itself.
 */
function charterReadyForOperations(bool $released = true): Booking
{
    return Booking::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'yacht_id' => Yacht::factory()->create()->id,
        'status' => 'confirmed',
        'operational_release_at' => $released ? now() : null,
    ]);
}

function assignCrewTo(Booking $booking, ?Crew $crew = null): CrewAssignment
{
    $crew ??= Crew::factory()->create();

    return CrewAssignment::factory()->create([
        'crew_id' => $crew->id,
        'booking_id' => $booking->id,
        'assignable_type' => $booking->getMorphClass(),
        'assignable_id' => $booking->id,
        'status' => 'proposed',
        'dispatched_at' => null,
    ]);
}

/* ── Dispatch ────────────────────────────────────────────────────────────── */

it('will not dispatch crew before Operational Release', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);
    $assignment = assignCrewTo(charterReadyForOperations(released: false));

    $gate = app(GateEvaluator::class)->forAction($assignment, 'crew-assignments.dispatch', $operations);

    expect($gate->verdict)->toBe('block')
        ->and($gate->failures)->toHaveCount(1)
        ->and($gate->failures[0]->message)->toContain('Operational Release');

    $this->actingAs($operations)
        ->post("/crew/assignments/{$assignment->id}/dispatch")
        ->assertSessionHasErrors('gate');

    expect($assignment->fresh()->dispatched_at)->toBeNull();
});

it('will not dispatch a crew member whose document has expired', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $crew = Crew::factory()->create(['first_name' => 'Marco', 'last_name' => 'Silva']);
    CrewDocument::factory()->create([
        'crew_id' => $crew->id,
        'type' => 'seaman_book',
        'expires_on' => now()->subDay()->toDateString(),
    ]);

    $assignment = assignCrewTo(charterReadyForOperations(), $crew);

    $gate = app(GateEvaluator::class)->forAction($assignment, 'crew-assignments.dispatch', $operations);

    // The failure names the person and the problem — not "conditions not met".
    expect($gate->verdict)->toBe('block')
        ->and($gate->failures[0]->message)->toContain('Marco Silva')
        ->and($gate->failures[0]->message)->toContain('expired');

    $this->actingAs($operations)
        ->post("/crew/assignments/{$assignment->id}/dispatch")
        ->assertSessionHasErrors('gate');
});

it('dispatches once the release is granted and the documents are valid', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $crew = Crew::factory()->create();
    CrewDocument::factory()->create([
        'crew_id' => $crew->id,
        'expires_on' => now()->addYear()->toDateString(),
    ]);

    $booking = charterReadyForOperations();
    $assignment = assignCrewTo($booking, $crew);

    $this->actingAs($operations)
        ->post("/crew/assignments/{$assignment->id}/dispatch")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $assignment->refresh();

    expect($assignment->dispatched_at)->not->toBeNull()
        ->and($assignment->status)->toBe('confirmed')
        ->and($assignment->dispatched_by)->toBe($operations->id)
        ->and($booking->activities()->where('summary', 'like', 'Crew dispatched%')->exists())->toBeTrue();
});

it('records an admin override with its reason when crew must sail anyway', function (): void {
    $admin = userWithRole(Roles::ADMIN);
    $assignment = assignCrewTo(charterReadyForOperations(released: false));

    app(DispatchCrew::class)->execute(
        $assignment,
        $admin,
        'Owner instructed departure; release paperwork following by 09:00.',
    );

    $override = GateOverride::latest('id')->first();

    expect($assignment->fresh()->dispatched_at)->not->toBeNull()
        ->and($override)->not->toBeNull()
        ->and($override->user_id)->toBe($admin->id)
        ->and($override->reason)->toContain('Owner instructed departure');
});

/* ── Boarding ────────────────────────────────────────────────────────────── */

it('will not board guests until every ID is verified and the briefing is logged', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);
    $booking = charterReadyForOperations();

    BookingGuest::factory()->count(3)->create([
        'booking_id' => $booking->id,
        'id_verified' => false,
    ]);

    $checklist = OperationsChecklist::factory()->create(['booking_id' => $booking->id]);
    $briefing = ChecklistItem::factory()->safetyBriefing()->create([
        'operations_checklist_id' => $checklist->id,
        'status' => 'pending',
    ]);

    $gate = app(GateEvaluator::class)->forAction($booking, 'bookings.board', $operations);

    expect($gate->verdict)->toBe('block')
        ->and($gate->failures)->toHaveCount(2)
        ->and($gate->failures[0]->message)->toContain('3 guests still unverified');

    $this->actingAs($operations)
        ->post("/charter/day/{$booking->id}/board")
        ->assertSessionHasErrors('gate');

    // Verify each guest, one tap each, the way the deck actually works.
    foreach ($booking->guests as $guest) {
        $this->actingAs($operations)
            ->post("/charter/day/{$booking->id}/guests/{$guest->id}/verify")
            ->assertRedirect();
    }

    // IDs done, briefing still outstanding: one failure left, named.
    $gate = app(GateEvaluator::class)->forAction($booking->fresh(), 'bookings.board', $operations);

    expect($gate->verdict)->toBe('block')
        ->and($gate->failures)->toHaveCount(1)
        ->and($gate->failures[0]->message)->toContain('afety briefing');

    $this->actingAs($operations)
        ->post("/charter/day/{$booking->id}/checklist/{$briefing->id}/complete", ['note' => 'Delivered on the aft deck'])
        ->assertRedirect();

    $this->actingAs($operations)
        ->post("/charter/day/{$booking->id}/board")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($booking->fresh()->boarded_at)->not->toBeNull();
});

/* ── The deposit ─────────────────────────────────────────────────────────── */

it('holds the security deposit while a damage inspection is open', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = charterReadyForOperations();

    $deposit = SecurityDeposit::factory()->create([
        'booking_id' => $booking->id,
        'amount' => 10000,
        'status' => 'held',
    ]);

    $damage = DamageReport::factory()->create([
        'booking_id' => $booking->id,
        'yacht_id' => $booking->yacht_id,
        'inspection_status' => 'pending',
        'estimated_cost' => 2500,
    ]);

    $this->actingAs($finance)
        ->post("/finance/security-deposits/{$deposit->id}/release")
        ->assertSessionHasErrors('gate');

    expect($deposit->fresh()->status)->toBe('held');

    // Closing the inspection is the act that releases the money.
    $operations = userWithRole(Roles::OPERATIONS);

    $this->actingAs($operations)
        ->post("/charter/damage-reports/{$damage->id}/close", [
            'resolution' => 'Tender fender replaced; cost recovered from the deposit.',
            'actual_cost' => 1800,
            'deduct_from_deposit' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($damage->fresh()->inspection_status)->toBe('closed');

    $this->actingAs($finance)
        ->post("/finance/security-deposits/{$deposit->id}/release", [
            'released_amount' => 8200,
            'deduction_reason' => 'Tender fender replacement',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $deposit->refresh();

    expect($deposit->status)->toBe('partially_released')
        ->and((float) $deposit->released_amount)->toBe(8200.0)
        ->and($deposit->deduction_reason)->toBe('Tender fender replacement')
        ->and($deposit->released_by)->toBe($finance->id);
});

it('releases the full deposit when the charter ends clean', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $deposit = SecurityDeposit::factory()->create([
        'booking_id' => charterReadyForOperations()->id,
        'amount' => 10000,
        'status' => 'held',
    ]);

    $this->actingAs($finance)
        ->post("/finance/security-deposits/{$deposit->id}/release")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($deposit->fresh()->status)->toBe('released')
        ->and((float) $deposit->fresh()->released_amount)->toBe(10000.0);
});

/* ── The screens themselves ──────────────────────────────────────────────── */

it('shows the crew expiry screen with the expired documents first', function (): void {
    $operations = userWithRole(Roles::OPERATIONS);

    $expired = Crew::factory()->create(['first_name' => 'Elena', 'last_name' => 'Expired']);
    CrewDocument::factory()->create(['crew_id' => $expired->id, 'expires_on' => now()->subWeek()->toDateString()]);

    $valid = Crew::factory()->create(['first_name' => 'Victor', 'last_name' => 'Valid']);
    CrewDocument::factory()->create(['crew_id' => $valid->id, 'expires_on' => now()->addYears(2)->toDateString()]);

    $this->actingAs($operations)
        ->get('/crew/expiry?days=60')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Crew/Expiry')
            ->where('days', 60)
            ->has('rows', 1)
            ->where('rows.0.crew', 'Elena Expired')
            ->where('rows.0.is_expired', true));
});

it('keeps a crew member off the dispatch list for another agent by role, not by luck', function (): void {
    $sales = userWithRole(Roles::SALES);

    // Sales has no business dispatching crew — the button is not merely hidden.
    $assignment = assignCrewTo(charterReadyForOperations());

    $this->actingAs($sales)
        ->post("/crew/assignments/{$assignment->id}/dispatch")
        ->assertForbidden();

    expect($assignment->fresh()->dispatched_at)->toBeNull();
});
