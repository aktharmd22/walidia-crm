<?php

declare(strict_types=1);

use App\Domain\Gates\GateEvaluator;
use App\Models\Client;
use App\Models\GateOverride;
use App\Models\Listing;
use App\Models\Nda;
use App\Models\Offer;
use App\Models\Transaction;
use App\Models\Viewing;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\GateRuleSeeder;

/*
|--------------------------------------------------------------------------
| Listing to ownership transfer
|--------------------------------------------------------------------------
|
| Three gates stand on this path, each protecting something a brokerage cannot
| get back: the seller's confidentiality, the seller's time off the market, and
| the brokerage's licence.
|
*/

beforeEach(function (): void {
    seedRoles();
    $this->seed(GateRuleSeeder::class);
});

function listingFor(array $attributes = []): Listing
{
    return Listing::factory()->create(array_merge([
        'yacht_id' => Yacht::factory()->create()->id,
    ], $attributes));
}

/* ── The NDA gate ────────────────────────────────────────────────────────── */

it('will not schedule a viewing without a signed NDA', function (): void {
    $broker = userWithRole(Roles::SALES);

    $listing = listingFor(['requires_nda' => true, 'assigned_user_id' => $broker->id]);
    $buyer = Client::factory()->verified()->create(['assigned_user_id' => $broker->id]);

    $viewing = Viewing::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'assigned_user_id' => $broker->id,
        'status' => 'requested',
    ]);

    $gate = app(GateEvaluator::class)->forTransition($viewing, 'status', 'scheduled', $broker);

    expect($gate->verdict)->toBe('block')
        ->and($gate->failures[0]->message)->toContain('NDA');

    $this->actingAs($broker)
        ->post("/brokerage/viewings/{$viewing->id}/schedule", [
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
        ])
        ->assertSessionHasErrors('gate');

    expect($viewing->fresh()->status)->toBe('requested');

    // Sign the NDA, and the same button works.
    Nda::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'signed_at' => now()->subDay(),
        'expires_on' => now()->addYear()->toDateString(),
        'status' => 'signed',
    ]);

    $this->actingAs($broker)
        ->post("/brokerage/viewings/{$viewing->id}/schedule", [
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($viewing->fresh()->status)->toBe('scheduled');
});

it('will not schedule a viewing for a buyer who has not passed KYC', function (): void {
    $broker = userWithRole(Roles::SALES);

    $listing = listingFor(['assigned_user_id' => $broker->id]);
    $buyer = Client::factory()->create([
        'assigned_user_id' => $broker->id,
        'kyc_status' => 'pending',
    ]);

    Nda::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'signed_at' => now()->subDay(),
        'status' => 'signed',
    ]);

    $viewing = Viewing::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'assigned_user_id' => $broker->id,
        'status' => 'requested',
    ]);

    $gate = app(GateEvaluator::class)->forTransition($viewing, 'status', 'scheduled', $broker);

    expect($gate->verdict)->toBe('block')
        ->and($gate->failures)->toHaveCount(1)
        ->and($gate->failures[0]->message)->toContain('KYC');
});

/* ── The proof-of-funds gate ─────────────────────────────────────────────── */

it('will not submit an offer without proof of funds where the mandate demands it', function (): void {
    $broker = userWithRole(Roles::SALES);

    $listing = listingFor(['requires_proof_of_funds' => true, 'assigned_user_id' => $broker->id]);
    $buyer = Client::factory()->verified()->create(['assigned_user_id' => $broker->id]);

    $offer = Offer::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'assigned_user_id' => $broker->id,
        'proof_of_funds_received' => false,
        'status' => 'draft',
    ]);

    $this->actingAs($broker)
        ->post("/brokerage/offers/{$offer->id}/submit")
        ->assertSessionHasErrors('gate');

    expect($offer->fresh()->status)->toBe('draft');

    $offer->forceFill(['proof_of_funds_received' => true])->save();

    $this->actingAs($broker)
        ->post("/brokerage/offers/{$offer->id}/submit")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($offer->fresh()->status)->toBe('submitted')
        ->and($listing->fresh()->status)->toBe('under_offer');
});

it('lets an open-listing offer through when the mandate does not require funds', function (): void {
    $broker = userWithRole(Roles::SALES);

    $listing = listingFor(['requires_proof_of_funds' => false, 'assigned_user_id' => $broker->id]);
    $buyer = Client::factory()->verified()->create(['assigned_user_id' => $broker->id]);

    $offer = Offer::factory()->create([
        'listing_id' => $listing->id,
        'client_id' => $buyer->id,
        'assigned_user_id' => $broker->id,
        'proof_of_funds_received' => false,
        'status' => 'draft',
    ]);

    $this->actingAs($broker)
        ->post("/brokerage/offers/{$offer->id}/submit")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($offer->fresh()->status)->toBe('submitted');
});

/* ── The ownership-transfer gate ─────────────────────────────────────────── */

it('will not transfer ownership before the money clears and AML is done', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    $listing = listingFor();
    $transaction = Transaction::factory()->create([
        'listing_id' => $listing->id,
        'buyer_client_id' => Client::factory()->verified()->create()->id,
        'agreed_price' => 11800000,
        'currency' => 'EUR',
        'aml_cleared' => false,
        'balance_cleared_at' => null,
    ]);

    $gate = app(GateEvaluator::class)->forAction($transaction, 'transactions.transfer-ownership', $admin);

    // Two separate failures, each naming its own remedy.
    expect($gate->verdict)->toBe('block')
        ->and($gate->failures)->toHaveCount(2);

    $this->actingAs($admin)
        ->post("/brokerage/transactions/{$transaction->id}/transfer-ownership")
        ->assertSessionHasErrors('gate');

    // The money arrives.
    $this->actingAs($admin)
        ->post("/brokerage/transactions/{$transaction->id}/funds", [
            'leg' => 'balance',
            'cleared_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect();

    // AML still outstanding: one failure left, and it says which.
    $gate = app(GateEvaluator::class)->forAction($transaction->fresh(), 'transactions.transfer-ownership', $admin);

    expect($gate->failures)->toHaveCount(1)
        ->and($gate->failures[0]->message)->toContain('AML');

    $this->actingAs($admin)
        ->post("/brokerage/transactions/{$transaction->id}/aml", [
            'notes' => 'Source of funds evidenced by bank letter and screened against sanctions lists.',
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post("/brokerage/transactions/{$transaction->id}/transfer-ownership")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $transaction->refresh();

    expect($transaction->isTransferred())->toBeTrue()
        ->and($transaction->status)->toBe('completed')
        ->and($listing->fresh()->status)->toBe('sold')
        ->and($listing->fresh()->is_published)->toBeFalse();
});

it('records who overrode the transfer gate, and why', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    $transaction = Transaction::factory()->create([
        'listing_id' => listingFor()->id,
        'agreed_price' => 500000,
        'aml_cleared' => false,
        'balance_cleared_at' => null,
    ]);

    $this->actingAs($admin)
        ->post("/brokerage/transactions/{$transaction->id}/transfer-ownership", [
            'override_reason' => 'Escrow agent confirmed settlement by SWIFT; reconciliation lands tomorrow.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $override = GateOverride::latest('id')->first();

    expect($transaction->fresh()->isTransferred())->toBeTrue()
        ->and($override)->not->toBeNull()
        ->and($override->user_id)->toBe($admin->id)
        ->and($override->reason)->toContain('Escrow agent confirmed');
});

/* ── The soft gate ───────────────────────────────────────────────────────── */

it('warns rather than blocks when a listing mandate is about to lapse', function (): void {
    $broker = userWithRole(Roles::SALES);

    $listing = listingFor([
        'assigned_user_id' => $broker->id,
        'agreement_expires_on' => now()->addDays(10)->toDateString(),
    ]);

    $gate = app(GateEvaluator::class)->forAction($listing, 'daily.expiry-scan', $broker);

    expect($gate->verdict)->toBe('warn')
        ->and($gate->failures[0]->message)->toContain('expires on');
});

/* ── The screens ─────────────────────────────────────────────────────────── */

it('serves a broker their brokerage screens', function (): void {
    $broker = userWithRole(Roles::SALES);

    foreach ([
        '/brokerage/listings' => 'Brokerage/Listings/Index',
        '/brokerage/buyer-requirements' => 'Brokerage/BuyerRequirements/Index',
        '/brokerage/ndas' => 'Brokerage/Ndas/Index',
        '/brokerage/viewings' => 'Brokerage/Viewings/Index',
        '/brokerage/offers' => 'Brokerage/Offers/Index',
    ] as $url => $component) {
        $this->actingAs($broker)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});

it('serves the transaction screens to an administrator', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    foreach ([
        '/brokerage/surveys' => 'Brokerage/Surveys/Index',
        '/brokerage/transactions' => 'Brokerage/Transactions/Index',
        '/brokerage/handovers' => 'Brokerage/Handovers/Index',
    ] as $url => $component) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});
