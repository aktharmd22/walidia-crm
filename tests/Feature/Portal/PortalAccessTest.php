<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\Client;
use App\Models\Crew;
use App\Models\CrewAssignment;
use App\Models\Listing;
use App\Models\ManagementAgreement;
use App\Models\OwnerStatement;
use App\Models\SignedLink;
use App\Models\Yacht;
use App\Support\Roles;

/*
|--------------------------------------------------------------------------
| The portals
|--------------------------------------------------------------------------
|
| The only doors an unauthenticated stranger can reach with a URL. Every test
| here is about what a link does NOT open.
|
*/

beforeEach(function (): void {
    seedRoles();
});

function statementWithLink(): array
{
    $yacht = Yacht::factory()->create();
    $agreement = ManagementAgreement::factory()->create(['yacht_id' => $yacht->id]);

    $statement = OwnerStatement::factory()->create([
        'management_agreement_id' => $agreement->id,
        'yacht_id' => $yacht->id,
        'status' => 'issued',
        'issued_at' => now(),
    ]);

    return [$statement, SignedLink::issue($statement, 'owner.statement')];
}

it('opens an owner statement with its link, and grants no session', function (): void {
    [$statement, $issued] = statementWithLink();

    $response = $this->get("/portal/statement/{$issued['token']}");

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Portal/OwnerStatement')
        ->where('statement.reference', $statement->reference));

    // No session, and nothing cached by a proxy or a shared browser.
    expect(auth()->check())->toBeFalse();
    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('no-store')
        ->and($cacheControl)->toContain('private');

    $response->assertHeader('Referrer-Policy', 'no-referrer');
});

it('records that the link was used, and by which address', function (): void {
    [, $issued] = statementWithLink();

    $this->get("/portal/statement/{$issued['token']}")->assertOk();

    $link = $issued['link']->fresh();

    expect($link->used_count)->toBe(1)
        ->and($link->last_used_at)->not->toBeNull()
        ->and($link->last_ip)->not->toBeNull();
});

it('refuses a link outside its purpose', function (): void {
    [, $issued] = statementWithLink();

    // A valid token, on the wrong door.
    $this->get("/portal/listing/{$issued['token']}")->assertNotFound();
    $this->get("/portal/assignment/{$issued['token']}")->assertNotFound();
});

it('refuses an expired link, a revoked one, and a guess — all identically', function (): void {
    [, $expired] = statementWithLink();
    $expired['link']->forceFill(['expires_at' => now()->subDay()])->save();
    $this->get("/portal/statement/{$expired['token']}")->assertNotFound();

    [, $revoked] = statementWithLink();
    $revoked['link']->forceFill(['revoked_at' => now()])->save();
    $this->get("/portal/statement/{$revoked['token']}")->assertNotFound();

    // A 404 as well, so a stranger learns nothing from the difference.
    $this->get('/portal/statement/'.str_repeat('a', 48))->assertNotFound();
});

it('never puts the reserve price in front of a partner broker', function (): void {
    $listing = Listing::factory()->create([
        'yacht_id' => Yacht::factory()->create()->id,
        'asking_price' => 12500000,
        'reserve_price' => 11000000,
    ]);

    $issued = SignedLink::issue($listing, 'partner.listing');

    $response = $this->get("/portal/listing/{$issued['token']}");

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Portal/Listing')
        ->where('listing.asking_price', fn ($value) => (float) $value === 12500000.0)
        ->missing('listing.reserve_price')
        ->missing('listing.yacht_owner_id'));

    // Belt and braces: the figure must not appear anywhere in the payload.
    expect($response->getContent())->not->toContain('11000000');
});

it('gives a crew member their sheet without the guests or the money', function (): void {
    $yacht = Yacht::factory()->create(['name' => 'Lady Walidia']);
    $booking = Booking::factory()->create([
        'client_id' => Client::factory()->create(['full_name' => 'HH Sheikh Confidential'])->id,
        'yacht_id' => $yacht->id,
    ]);

    $assignment = CrewAssignment::factory()->create([
        'crew_id' => Crew::factory()->create(['first_name' => 'Marco', 'last_name' => 'Silva'])->id,
        'booking_id' => $booking->id,
        'assignable_type' => $booking->getMorphClass(),
        'assignable_id' => $booking->id,
        'day_rate' => 1800,
    ]);

    $issued = SignedLink::issue($assignment, 'crew.assignment');

    $response = $this->get("/portal/assignment/{$issued['token']}");

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Portal/CrewAssignment')
        ->where('assignment.crew', 'Marco Silva')
        ->where('assignment.yacht', 'Lady Walidia')
        ->missing('assignment.day_rate'));

    // The client's name is the thing that must never travel to a crew phone.
    expect($response->getContent())
        ->not->toContain('Sheikh Confidential')
        ->not->toContain('1800');
});

it('shows a staff member the client view, not their own', function (): void {
    $admin = userWithRole(Roles::ADMIN);
    [, $issued] = statementWithLink();

    // Signed in as an administrator, the portal still forgets them entirely.
    $this->actingAs($admin)
        ->get("/portal/statement/{$issued['token']}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Portal/OwnerStatement'));

    // The administrator's own identity does not travel into the portal payload.
    expect(auth()->check())->toBeFalse();
});

it('issues a link that expires in seven days and is never recoverable', function (): void {
    $finance = userWithRole(Roles::FINANCE);
    [$statement] = statementWithLink();

    $this->actingAs($finance)
        ->post("/management/owner-statements/{$statement->id}/share")
        ->assertRedirect()
        ->assertSessionHas('portal_link');

    $link = SignedLink::latest('id')->first();

    expect($link->purpose)->toBe('owner.statement')
        ->and($link->expires_at->diffInDays(now()))->toBeLessThanOrEqual(7)
        // Only the hash is stored: the token cannot be read back out.
        ->and($link->getAttributes())->not->toHaveKey('token');
});
