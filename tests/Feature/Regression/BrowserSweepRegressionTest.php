<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\Client;
use App\Models\Yacht;
use App\Support\Roles;

/**
 * What driving the application through a real browser turned up.
 *
 * Every one of these passed the PHP suite and failed in Chrome, which is the
 * whole point of writing them down: the assertions the suite was making never
 * reached the thing that broke.
 */
beforeEach(function (): void {
    seedRoles();
    $this->actingAs(userWithRole(Roles::ADMIN));
});

it('sends a signed-in user to a page that exists', function (): void {
    // Fortify ships pointing at /home, which this application does not have,
    // so every successful sign-in landed on a 404.
    expect(config('fortify.home'))->toBe('/');

    $this->get(config('fortify.home'))->assertSuccessful();
});

it('eager-loads the same relations when exporting as when listing', function (string $url): void {
    // Lazy loading is disabled, so an export whose rows touch a relation the
    // query did not load throws rather than downloading.
    $this->get($url)->assertSuccessful();
})->with([
    '/charter/bookings/export',
    '/charter/cost-sheets/export',
    '/tasks/export',
    '/leads/export',
    '/clients/export',
]);

it('lists cost sheets without lazy loading their lines', function (): void {
    $this->get('/charter/cost-sheets')->assertSuccessful();
});

it('always sends the payment schedule as an array', function (): void {
    // A booking with no schedule sent null, and a React default parameter only
    // fills in for undefined — so the detail screen threw on render.
    $booking = Booking::factory()->create([
        'client_id' => Client::factory(),
        'yacht_id' => Yacht::factory(),
    ]);

    expect($booking->paymentSchedule)->toBeNull();

    $this->get("/charter/bookings/{$booking->id}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('schedule', []));
});
