<?php

declare(strict_types=1);

use App\Domain\Gates\Exceptions\GateBlockedException;
use App\Domain\Gates\GateEvaluator;
use App\Models\Booking;
use App\Models\Client;
use App\Models\GateEvaluation;
use App\Models\GateOverride;
use App\Models\GateRule;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\PaymentScheduleItem;
use App\Models\Task;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\GateRuleSeeder;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| The gate engine
|--------------------------------------------------------------------------
|
| These are the tests that matter most (brief §12). For each rule: a passing
| case, a blocking case asserting the message the user actually sees, the
| override path, and the permission that guards it.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(GateRuleSeeder::class);
});

/** A booking with a deposit instalment and no payment against it. */
function bookingAwaitingDeposit(?Client $client = null): Booking
{
    $booking = Booking::factory()->create([
        'client_id' => $client?->id ?? Client::factory()->create()->id,
        'yacht_id' => Yacht::factory()->create()->id,
    ]);

    $schedule = PaymentSchedule::factory()->create(['booking_id' => $booking->id]);
    PaymentScheduleItem::factory()->create([
        'payment_schedule_id' => $schedule->id,
        'label' => 'deposit',
        'amount' => 21000,
    ]);

    return $booking->fresh();
}

function clearDeposit(Booking $booking, float $amount = 21000): Payment
{
    $item = PaymentScheduleItem::whereHas('schedule', fn ($query) => $query->where('booking_id', $booking->id))
        ->where('label', 'deposit')
        ->firstOrFail();

    $payment = Payment::factory()->create([
        'client_id' => $booking->client_id,
        'amount' => $amount,
        'status' => 'cleared',
        'cleared_at' => now(),
    ]);

    $payment->allocations()->create([
        'payment_schedule_item_id' => $item->id,
        'amount' => $amount,
    ]);

    return $payment;
}

/* ── Operational Release: the hinge ──────────────────────────────────────── */

it('blocks Operational Release while the deposit has not cleared', function (): void {
    $booking = bookingAwaitingDeposit();
    $finance = userWithRole(Roles::FINANCE);

    $result = app(GateEvaluator::class)->forAction($booking, 'bookings.release-operations', $finance);

    expect($result->blocked())->toBeTrue()
        ->and($result->failures)->toHaveCount(1)
        ->and($result->failures[0]->message)->toContain('Deposit not cleared')
        ->and($result->failures[0]->resolutionUrl)->not->toBeNull();
});

it('names the shortfall in the blocking message, not just "not allowed"', function (): void {
    $booking = bookingAwaitingDeposit();
    clearDeposit($booking, 15000);   // part paid against a 21,000 deposit

    $result = app(GateEvaluator::class)->forAction($booking, 'bookings.release-operations', userWithRole(Roles::FINANCE));

    expect($result->blocked())->toBeTrue()
        ->and($result->failures[0]->message)->toContain('15,000.00')
        ->and($result->failures[0]->message)->toContain('21,000.00');
});

it('does not count a payment that has not cleared', function (): void {
    $booking = bookingAwaitingDeposit();

    $payment = Payment::factory()->create(['amount' => 21000, 'status' => 'pending', 'cleared_at' => null]);
    $item = PaymentScheduleItem::whereHas('schedule', fn ($q) => $q->where('booking_id', $booking->id))->first();
    $payment->allocations()->create(['payment_schedule_item_id' => $item->id, 'amount' => 21000]);

    $result = app(GateEvaluator::class)->forAction($booking, 'bookings.release-operations', userWithRole(Roles::FINANCE));

    expect($result->blocked())->toBeTrue();
});

it('passes Operational Release once the deposit has cleared', function (): void {
    $booking = bookingAwaitingDeposit();
    clearDeposit($booking);

    $result = app(GateEvaluator::class)->forAction($booking, 'bookings.release-operations', userWithRole(Roles::FINANCE));

    expect($result->verdict)->toBe('pass');
});

it('refuses the release over HTTP while blocked, and grants it once cleared', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = bookingAwaitingDeposit();

    $this->actingAs($finance)
        ->post("/charter/bookings/{$booking->id}/release-operations")
        ->assertSessionHasErrors('gate');

    expect($booking->fresh()->operational_release_at)->toBeNull();

    clearDeposit($booking);

    $this->actingAs($finance)
        ->post("/charter/bookings/{$booking->id}/release-operations")
        ->assertRedirect();

    expect($booking->fresh()->operational_release_at)->not->toBeNull();
});

it('lets only Finance and Admin grant Operational Release', function (string $role, bool $allowed): void {
    $user = userWithRole($role);
    $booking = bookingAwaitingDeposit();
    // Assigned to the acting user, so this measures permission rather than
    // visibility — an unowned record would 404 before the policy ran.
    $booking->forceFill(['assigned_user_id' => $user->id])->save();
    clearDeposit($booking);

    $response = $this->actingAs($user)
        ->post("/charter/bookings/{$booking->id}/release-operations");

    $allowed ? $response->assertRedirect() : $response->assertForbidden();
})->with([
    [Roles::SALES, false],
    [Roles::OPERATIONS, false],
    [Roles::FINANCE, true],
    [Roles::ADMIN, true],
]);

/* ── Overrides ───────────────────────────────────────────────────────────── */

it('lets an admin override a hard gate, and writes it to the register', function (): void {
    $admin = userWithRole(Roles::ADMIN);
    $booking = bookingAwaitingDeposit();

    $this->actingAs($admin)
        ->post("/charter/bookings/{$booking->id}/release-operations", [
            'override_reason' => 'Owner instructed release; funds confirmed by the bank on the phone.',
        ])
        ->assertRedirect();

    expect($booking->fresh()->operational_release_at)->not->toBeNull();

    $override = GateOverride::first();

    expect($override)->not->toBeNull()
        ->and($override->user_id)->toBe($admin->id)
        ->and($override->reason)->toContain('Owner instructed release')
        ->and($override->subject_type)->toBe('booking')
        ->and($override->ip_address)->not->toBeNull();
});

it('refuses an override from someone without the permission', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = bookingAwaitingDeposit();

    $this->actingAs($finance)
        ->post("/charter/bookings/{$booking->id}/release-operations", [
            'override_reason' => 'We are quite sure the money is on its way to us.',
        ])
        ->assertForbidden();

    expect($booking->fresh()->operational_release_at)->toBeNull()
        ->and(GateOverride::count())->toBe(0);
});

it('refuses an override with a token reason', function (): void {
    $admin = userWithRole(Roles::ADMIN);
    $booking = bookingAwaitingDeposit();

    expect(fn () => app(GateEvaluator::class)->override($booking, 'bookings.release-operations', $admin, 'ok'))
        ->toThrow(HttpException::class);

    expect(GateOverride::count())->toBe(0);
});

/* ── The record of every decision ────────────────────────────────────────── */

it('records every evaluation, passing as well as failing', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = bookingAwaitingDeposit();
    $evaluator = app(GateEvaluator::class);

    $evaluator->forAction($booking, 'bookings.release-operations', $finance);
    clearDeposit($booking);
    $evaluator->forAction($booking->fresh(), 'bookings.release-operations', $finance);

    $results = GateEvaluation::orderBy('id')->pluck('result')->all();

    expect($results)->toBe(['block', 'pass'])
        ->and(GateEvaluation::first()->failed_conditions)->not->toBeNull()
        ->and(GateEvaluation::first()->user_id)->toBe($finance->id);
});

/* ── KYC gate on contract generation ─────────────────────────────────────── */

it('blocks contract generation until KYC is verified, and passes once it is', function (): void {
    $sales = userWithRole(Roles::SALES);
    $client = Client::factory()->create(['assigned_user_id' => $sales->id, 'kyc_status' => 'pending']);
    $booking = bookingAwaitingDeposit($client);

    $blocked = app(GateEvaluator::class)->forAction($booking, 'bookings.generate-contract', $sales);

    expect($blocked->blocked())->toBeTrue()
        ->and($blocked->failures[0]->message)->toContain('not yet verified');

    $client->forceFill(['kyc_status' => 'verified', 'kyc_verified_at' => now()])->save();

    expect(app(GateEvaluator::class)->forAction($booking->fresh(), 'bookings.generate-contract', $sales)->verdict)
        ->toBe('pass');
});

it('treats expired KYC as unverified', function (): void {
    $sales = userWithRole(Roles::SALES);
    $client = Client::factory()->create([
        'assigned_user_id' => $sales->id,
        'kyc_status' => 'verified',
        'kyc_verified_at' => now()->subYears(3),
        'kyc_expires_on' => now()->subMonth(),
    ]);

    $result = app(GateEvaluator::class)
        ->forAction(bookingAwaitingDeposit($client), 'bookings.generate-contract', $sales);

    expect($result->blocked())->toBeTrue()
        ->and($result->failures[0]->message)->toContain('expired');
});

/* ── Soft gates ──────────────────────────────────────────────────────────── */

it('warns rather than blocks when a booking is confirmed without an itinerary, and raises a task', function (): void {
    $sales = userWithRole(Roles::SALES);
    $booking = bookingAwaitingDeposit();
    $booking->forceFill(['itinerary' => null])->save();

    $result = app(GateEvaluator::class)->forTransition($booking, 'status', 'confirmed', $sales);

    expect($result->verdict)->toBe('warn')
        ->and($result->blocked())->toBeFalse()
        ->and($result->failures[0]->message)->toContain('itinerary');

    $task = Task::where('source', 'gate')->first();

    expect($task)->not->toBeNull()
        ->and($task->title)->toBe('Add itinerary')
        ->and($task->subject_id)->toBe($booking->id);
});

it('does not raise the same soft-gate task twice', function (): void {
    $sales = userWithRole(Roles::SALES);
    $booking = bookingAwaitingDeposit();
    $evaluator = app(GateEvaluator::class);

    $evaluator->forTransition($booking, 'status', 'confirmed', $sales);
    $evaluator->forTransition($booking, 'status', 'confirmed', $sales);

    expect(Task::where('source', 'gate')->count())->toBe(1);
});

/* ── Engine behaviour ────────────────────────────────────────────────────── */

it('skips a rule that has been switched off', function (): void {
    GateRule::where('key', 'charter.operational.release')->update(['is_active' => false]);
    cache()->flush();

    $result = app(GateEvaluator::class)
        ->forAction(bookingAwaitingDeposit(), 'bookings.release-operations', userWithRole(Roles::FINANCE));

    expect($result->verdict)->toBe('pass');
});

it('fails loudly when a rule refers to a check nobody implemented', function (): void {
    GateRule::where('key', 'charter.operational.release')->update([
        'conditions' => [['check' => 'nonexistent.check', 'params' => []]],
    ]);
    cache()->flush();

    $result = app(GateEvaluator::class)
        ->forAction(bookingAwaitingDeposit(), 'bookings.release-operations', userWithRole(Roles::FINANCE));

    expect($result->blocked())->toBeTrue()
        ->and($result->failures[0]->message)->toContain('not implemented');
});

it('throws rather than returning on the write path', function (): void {
    $booking = bookingAwaitingDeposit();

    expect(fn () => app(GateEvaluator::class)
        ->assertAction($booking, 'bookings.release-operations', userWithRole(Roles::FINANCE)))
        ->toThrow(GateBlockedException::class);
});

it('answers the dry-run endpoint the screens use to explain a disabled button', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = bookingAwaitingDeposit();

    $this->actingAs($finance)
        ->postJson('/gates/evaluate', [
            'subject_type' => 'booking',
            'subject_id' => $booking->id,
            'action' => 'bookings.release-operations',
        ])
        ->assertOk()
        ->assertJsonPath('verdict', 'block')
        ->assertJsonPath('overridable', true)
        ->assertJsonStructure(['verdict', 'overridable', 'failures' => [['rule', 'condition', 'message', 'resolution']]]);
});
