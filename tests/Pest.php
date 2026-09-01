<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test case
|--------------------------------------------------------------------------
|
| Feature tests hit a real database: the gate engine, the ownership scopes and
| the permission matrix are all database behaviour, and a mocked repository
| would prove nothing about them.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

/**
 * A user holding the given role, with two-factor already confirmed so tests
 * exercise the screen under test rather than the enrolment redirect.
 */
function userWithRole(string $role, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'two_factor_confirmed_at' => now(),
        'two_factor_secret' => encrypt('TESTSECRET'),
    ], $attributes));

    $user->assignRole($role);

    return $user->fresh();
}

function seedRoles(): void
{
    test()->seed(RolesAndPermissionsSeeder::class);
}
