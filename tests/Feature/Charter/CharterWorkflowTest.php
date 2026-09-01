<?php

declare(strict_types=1);

use App\Domain\Charter\Actions\AcceptProposal;
use App\Domain\Charter\CharterMatcher;
use App\Domain\Charter\CostSheetCalculator;
use App\Domain\Finance\TaxCalculator;
use App\Models\Booking;
use App\Models\CharterEnquiry;
use App\Models\CharterProposal;
use App\Models\Client;
use App\Models\CostSheet;
use App\Models\Invoice;
use App\Models\Marina;
use App\Models\Payment;
use App\Models\ProposalItem;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\FinanceDefaultsSeeder;
use Database\Seeders\GateRuleSeeder;

/*
|--------------------------------------------------------------------------
| Enquiry to charter, end to end
|--------------------------------------------------------------------------
|
| The path the business actually walks: enquiry → matching → proposal →
| acceptance → booking → invoice → cleared payment → Operational Release.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(GateRuleSeeder::class);
    $this->seed(FinanceDefaultsSeeder::class);
});

it('walks an enquiry through to a released charter', function (): void {
    $sales = userWithRole(Roles::SALES);
    $finance = userWithRole(Roles::FINANCE);

    $marina = Marina::factory()->create(['timezone' => 'Asia/Dubai']);
    $yacht = Yacht::factory()->create(['home_marina_id' => $marina->id, 'capacity_cruising' => 20]);
    $yacht->charterProfile()->create(['full_day_rate' => 40000, 'currency' => 'AED', 'is_bookable' => true]);

    $client = Client::factory()->verified()->create(['assigned_user_id' => $sales->id]);

    // 1. The enquiry.
    $enquiry = CharterEnquiry::factory()->create([
        'client_id' => $client->id,
        'assigned_user_id' => $sales->id,
        'pickup_marina_id' => $marina->id,
        'guests_adults' => 12,
        'guests_children' => 0,
        'requested_date' => now()->addWeeks(3)->toDateString(),
        'duration_hours' => 8,
        'budget_max' => 60000,
    ]);

    // 2. Matching — availability is a hard filter, scores are explainable.
    $matches = app(CharterMatcher::class)->match($enquiry);

    expect($matches)->toHaveCount(1)
        ->and($matches->first()->yacht_id)->toBe($yacht->id)
        ->and($matches->first()->reasons)->not->toBeEmpty()
        ->and($enquiry->fresh()->status)->toBe('matching');

    // 3. The proposal, priced server-side.
    $proposal = CharterProposal::factory()->create([
        'charter_enquiry_id' => $enquiry->id,
        'client_id' => $client->id,
        'assigned_user_id' => $sales->id,
        'status' => 'sent',
    ]);

    ProposalItem::factory()->create([
        'charter_proposal_id' => $proposal->id,
        'yacht_id' => $yacht->id,
        'type' => 'charter',
    ]);

    // 4. Acceptance: locks the yacht, opens the booking, lays the schedule.
    $booking = app(AcceptProposal::class)->execute($proposal->fresh(), $sales);

    expect($booking->reference)->toStartWith('BK-')
        ->and($booking->yacht_id)->toBe($yacht->id)
        ->and($booking->status)->toBe('pending_contract')
        ->and($enquiry->fresh()->status)->toBe('won')
        ->and($yacht->fresh()->isAvailableBetween($booking->starts_at, $booking->ends_at))->toBeFalse()
        ->and($booking->paymentSchedule->items)->toHaveCount(2)
        ->and((float) $booking->paymentSchedule->items->firstWhere('label', 'deposit')->amount)->toBe(21000.0);

    // 5. Operational Release is blocked until the deposit clears.
    $this->actingAs($finance)
        ->post("/charter/bookings/{$booking->id}/release-operations")
        ->assertSessionHasErrors('gate');

    // 6. Money in, and cleared.
    $deposit = $booking->paymentSchedule->items->firstWhere('label', 'deposit');

    $this->actingAs($finance)->post('/finance/payments', [
        'client_id' => $client->id,
        'method' => 'bank_transfer',
        'amount' => 21000,
        'currency' => 'AED',
        'received_at' => now()->toDateTimeString(),
        'allocations' => [
            ['payment_schedule_item_id' => $deposit->id, 'amount' => 21000],
        ],
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();

    expect($payment->status)->toBe('pending');

    $this->actingAs($finance)
        ->post("/finance/payments/{$payment->id}/confirm-deposit")
        ->assertRedirect();

    expect($payment->fresh()->isCleared())->toBeTrue()
        ->and($payment->fresh()->receipt)->not->toBeNull()
        ->and($deposit->fresh()->status)->toBe('paid');

    // 7. Now the release goes through.
    $this->actingAs($finance)
        ->post("/charter/bookings/{$booking->id}/release-operations")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $booking->refresh();

    expect($booking->operational_release_at)->not->toBeNull()
        ->and($booking->operational_release_by)->toBe($finance->id)
        ->and($booking->activities()->where('summary', 'Operational Release granted')->exists())->toBeTrue();
});

it('cancels a booking and gives the yacht back to the calendar', function (): void {
    $sales = userWithRole(Roles::SALES);
    $yacht = Yacht::factory()->create();
    $booking = Booking::factory()->create([
        'client_id' => Client::factory()->create(['assigned_user_id' => $sales->id])->id,
        'yacht_id' => $yacht->id,
        'assigned_user_id' => $sales->id,
    ]);

    $yacht->availabilityBlocks()->create([
        'starts_at' => $booking->starts_at,
        'ends_at' => $booking->ends_at,
        'type' => 'booking',
        'source_type' => $booking->getMorphClass(),
        'source_id' => $booking->id,
    ]);

    expect($yacht->isAvailableBetween($booking->starts_at, $booking->ends_at))->toBeFalse();

    $this->actingAs($sales)
        ->post("/charter/bookings/{$booking->id}/cancel", ['reason' => 'Client postponed to next season'])
        ->assertRedirect();

    expect($booking->fresh()->status)->toBe('cancelled')
        ->and($yacht->fresh()->isAvailableBetween($booking->starts_at, $booking->ends_at))->toBeTrue();
});

/* ── Money ───────────────────────────────────────────────────────────────── */

it('applies 5% VAT to a standard line and none to a security deposit', function (): void {
    $tax = app(TaxCalculator::class);

    $charter = $tax->line(1, 40000, 'yacht_fee');
    $deposit = $tax->line(1, 10000, 'security_deposit');
    $tips = $tax->line(1, 2000, 'crew_tips');

    expect($charter['tax_amount'])->toBe(2000.0)
        ->and($charter['line_total'])->toBe(42000.0)
        ->and($deposit['tax_amount'])->toBe(0.0)
        ->and($deposit['tax_treatment'])->toBe('out_of_scope')
        ->and($tips['tax_amount'])->toBe(0.0);
});

it('rounds tax once per line, not on a running total', function (): void {
    $tax = app(TaxCalculator::class);

    // Three lines that each round the same way must sum to the same figure
    // whichever order they are added in.
    $lines = [$tax->line(3, 333.33, 'yacht_fee'), $tax->line(1, 0.99, 'food'), $tax->line(7, 12.345, 'beverages')];
    $total = array_sum(array_column($lines, 'line_total'));

    expect(round($total, 2))->toBe($total);
});

it('computes charter profit and the quoted-to-actual variance', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $booking = Booking::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'yacht_id' => Yacht::factory()->create()->id,
    ]);

    $sheet = CostSheet::factory()->create(['booking_id' => $booking->id]);

    $sheet->lines()->createMany([
        ['phase' => 'quoted', 'section' => 'revenue', 'category' => 'yacht_fee', 'amount' => 40000],
        ['phase' => 'quoted', 'section' => 'cost', 'category' => 'crew_tips', 'amount' => 2000],
        ['phase' => 'actual', 'section' => 'revenue', 'category' => 'yacht_fee', 'amount' => 40000],
        ['phase' => 'actual', 'section' => 'cost', 'category' => 'crew_tips', 'amount' => 3500],
    ]);

    $calculator = app(CostSheetCalculator::class);
    $totals = $calculator->recalculate($sheet->fresh());

    // Actuals win where they exist.
    expect($totals['offer'])->toBe(40000.0)
        ->and($totals['cost'])->toBe(3500.0)
        ->and($totals['profit'])->toBe(36500.0);

    $variance = collect($calculator->variance($sheet->fresh()))->firstWhere('category', 'crew_tips');

    // Spending 1,500 more than quoted is a negative variance.
    expect($variance['variance'])->toBe(-1500.0);
});

it('never edits an issued invoice, and credits it instead', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    $client = Client::factory()->create();

    $this->actingAs($finance)->post('/finance/invoices', [
        'type' => 'tax_invoice',
        'client_id' => $client->id,
        'tax_treatment' => 'standard',
        'currency' => 'AED',
        'items' => [['description_en' => 'Full day charter', 'category' => 'yacht_fee', 'quantity' => 1, 'unit_price' => 40000]],
    ])->assertRedirect();

    $invoice = Invoice::latest('id')->first();

    expect($invoice->status)->toBe('draft')
        ->and($invoice->reference)->toBeNull()
        ->and((float) $invoice->total)->toBe(42000.0);

    $this->actingAs($finance)->post("/finance/invoices/{$invoice->id}/issue")->assertRedirect();

    $invoice->refresh();

    expect($invoice->status)->toBe('issued')
        ->and($invoice->reference)->toStartWith('INV-');

    // Editing an issued invoice is refused by the policy, not merely hidden.
    $this->actingAs($finance)->put("/finance/invoices/{$invoice->id}", [
        'type' => 'tax_invoice',
        'tax_treatment' => 'standard',
        'currency' => 'AED',
        'items' => [['description_en' => 'Changed', 'quantity' => 1, 'unit_price' => 1]],
    ])->assertForbidden();

    $this->actingAs($finance)->post("/finance/invoices/{$invoice->id}/credit-note")->assertRedirect();

    $credit = Invoice::where('type', 'credit_note')->first();

    expect($credit)->not->toBeNull()
        ->and((float) $credit->total)->toBe(-42000.0)
        ->and($credit->reference)->toStartWith('CN-')
        ->and($invoice->fresh()->status)->toBe('credited');
});

it('issues gapless invoice numbers', function (): void {
    $finance = userWithRole(Roles::FINANCE);

    $references = collect(range(1, 3))->map(function () use ($finance): string {
        $invoice = Invoice::factory()->create(['status' => 'draft', 'reference' => null]);
        $invoice->items()->create(['description_en' => 'Charter', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000]);

        $this->actingAs($finance)->post("/finance/invoices/{$invoice->id}/issue");

        return (string) $invoice->fresh()->reference;
    });

    $numbers = $references->map(fn (string $reference): int => (int) substr($reference, -5));

    expect($numbers->all())->toBe([1, 2, 3]);
});
