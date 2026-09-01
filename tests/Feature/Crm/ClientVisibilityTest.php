<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Lead;
use App\Models\Team;
use App\Support\Roles;

beforeEach(function (): void {
    seedRoles();
});

/*
|--------------------------------------------------------------------------
| Record visibility (D-017)
|--------------------------------------------------------------------------
|
| The brief requires that a Sales user cannot retrieve another agent's client
| even by guessing an ID. These are the tests that hold that line: a record
| outside the visible set is not there at all — 404, never 403, because a 403
| confirms the record exists.
|
*/

it('lets a sales user see only their own clients on the index', function (): void {
    $mine = userWithRole(Roles::SALES);
    $theirs = userWithRole(Roles::SALES);

    Client::factory()->count(3)->create(['assigned_user_id' => $mine->id]);
    Client::factory()->count(2)->create(['assigned_user_id' => $theirs->id]);

    $this->actingAs($mine)
        ->get('/clients')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Clients/Index')
            ->has('rows.data', 3));
});

it('returns 404, not 403, when a sales user guesses another agent id', function (string $method, string $suffix): void {
    $mine = userWithRole(Roles::SALES);
    $theirs = userWithRole(Roles::SALES);

    $client = Client::factory()->create(['assigned_user_id' => $theirs->id]);

    $this->actingAs($mine)
        ->call($method, "/clients/{$client->id}{$suffix}")
        ->assertNotFound();
})->with([
    ['GET', ''],
    ['GET', '/edit'],
    ['PUT', ''],
    ['DELETE', ''],
    ['GET', '/timeline'],
]);

it('keeps another agent records out of global search results', function (): void {
    $mine = userWithRole(Roles::SALES);
    $theirs = userWithRole(Roles::SALES);

    Client::factory()->create(['assigned_user_id' => $theirs->id, 'first_name' => 'Zubaida', 'last_name' => 'Rahman']);

    $this->actingAs($mine)
        ->get('/clients?search=Zubaida')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('rows.data', 0));
});

it('lets operations and finance see every client, because their work spans the book', function (string $role): void {
    $sales = userWithRole(Roles::SALES);
    Client::factory()->count(4)->create(['assigned_user_id' => $sales->id]);

    $this->actingAs(userWithRole($role))
        ->get('/clients')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('rows.data', 4));
})->with([Roles::OPERATIONS, Roles::FINANCE]);

it('lets a team lead see the team book', function (): void {
    $lead = userWithRole(Roles::SALES);
    $member = userWithRole(Roles::SALES);
    $lead->givePermissionTo('records.view-team');

    $team = Team::factory()->create(['lead_user_id' => $lead->id]);
    $team->members()->attach([$lead->id, $member->id]);

    Client::factory()->count(2)->create(['assigned_user_id' => $member->id]);
    Client::factory()->create(['assigned_user_id' => $lead->id]);

    $this->actingAs($lead->fresh())
        ->get('/clients')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('rows.data', 3));
});

it('hides unassigned clients from sales but shows unassigned leads', function (): void {
    $sales = userWithRole(Roles::SALES);

    Client::factory()->create(['assigned_user_id' => null]);
    Lead::factory()->create(['assigned_user_id' => null]);

    // A client with no owner is not a shared queue; an unassigned lead is.
    $this->actingAs($sales)->get('/clients')->assertInertia(fn ($page) => $page->has('rows.data', 0));
    $this->actingAs($sales)->get('/leads')->assertInertia(fn ($page) => $page->has('rows.data', 1));
});
