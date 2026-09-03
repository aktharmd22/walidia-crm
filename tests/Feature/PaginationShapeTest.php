<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Yacht;
use App\Support\Roles;

/*
|--------------------------------------------------------------------------
| The paginated-list contract
|--------------------------------------------------------------------------
|
| The front end reads `rows.meta.last_page`. Laravel's paginator puts its
| counters at the top level and has no `meta` key at all, so serving one
| directly threw `Cannot read properties of undefined` and rendered every
| index screen in the application as a blank page — while the server tests,
| which only assert on the payload, stayed green.
|
| A whole application of blank screens is too much to leave resting on
| everyone remembering App\Support\Paginate. So the shape is asserted here,
| and asserted on a real screen rather than in the abstract.
|
*/

beforeEach(function (): void {
    seedRoles();
});

it('gives every paginated screen the shape the front end reads', function (string $url): void {
    $admin = userWithRole(Roles::ADMIN);

    Client::factory()->count(3)->create();
    Yacht::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get($url)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows.data')
            ->has('rows.links')
            // The three the Pagination component actually touches.
            ->has('rows.meta.current_page')
            ->has('rows.meta.last_page')
            ->has('rows.meta.total'));
})->with([
    '/clients',
    '/leads',
    '/leads/inbox',
    '/fleet/yachts',
    '/charter/bookings',
    '/charter/enquiries',
    '/finance/invoices',
    '/crew',
    '/vendors',
    '/brokerage/listings',
    '/brokerage/offers',
    '/management/certificates',
    '/engine/workflows',
    '/engine/communications',
    '/tasks',
]);

it('gives an archive screen the same shape', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    Client::factory()->create()->delete();

    $this->actingAs($admin)
        ->get('/clients/archive')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('rows.meta.last_page'));
});

it('shapes a paginator built outside the shared spine', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    // These build their own paginators rather than going through
    // ResourceController::paginate(), which is exactly how one gets missed.
    foreach (['/crew/assignments', '/crew/payouts', '/compliance/overrides'] as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('rows.meta.last_page'));
    }
});
