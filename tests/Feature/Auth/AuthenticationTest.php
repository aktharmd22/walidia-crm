<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Roles;

beforeEach(function (): void {
    seedRoles();
});

it('shows the sign-in screen', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Walidia', escape: false);
});

it('signs a user in with valid credentials', function (): void {
    $user = User::factory()->create(['password' => 'Harbour!Passage2026']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Harbour!Passage2026',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

it('refuses an inactive account even with the right password', function (): void {
    $user = User::factory()->create([
        'password' => 'Harbour!Passage2026',
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Harbour!Passage2026',
    ])->assertSessionHasErrors();

    $this->assertGuest();
});

it('rate limits sign-in after five attempts', function (): void {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    // The sixth attempt is refused by the throttle middleware itself, before
    // the credentials are examined at all.
    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(429);

    $this->assertGuest();
});

it('sends a user without confirmed two-factor to enrolment, not the dashboard', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SALES);

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('two-factor.setup'));
});

it('lets a user with confirmed two-factor reach the dashboard', function (): void {
    $user = userWithRole(Roles::SALES);

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('keeps the enrolment screen reachable while two-factor is outstanding', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SALES);

    $this->actingAs($user)
        ->get(route('two-factor.setup'))
        ->assertOk();
});

it('requires authentication for every application route', function (string $uri): void {
    $this->get($uri)->assertRedirect('/login');
})->with([
    '/',
    '/dashboard/alerts',
    '/dashboard/calendar',
    '/me/profile',
    '/me/sessions',
    '/search?q=test',
]);
