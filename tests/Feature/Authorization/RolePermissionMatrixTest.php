<?php

declare(strict_types=1);

use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    seedRoles();
});

it('seeds every permission in the catalogue', function (): void {
    expect(Permission::count())->toBe(count(Permissions::all()));
});

it('seeds the four roles and no others', function (): void {
    expect(Role::pluck('name')->sort()->values()->all())
        ->toBe(collect(Roles::all())->sort()->values()->all());
});

it('grants no unknown permission in the role matrix', function (string $role): void {
    expect(fn () => Roles::permissionsFor($role))->not->toThrow(InvalidArgumentException::class);
})->with(Roles::all());

it('gives admin every ability, including ones added later', function (): void {
    $admin = userWithRole(Roles::ADMIN);

    // Granted by the Gate::before check, so a permission added in a later
    // phase is never silently missing from Admin.
    expect($admin->can('a-permission-that-does-not-exist-yet.view'))->toBeTrue()
        ->and($admin->can('gates.override'))->toBeTrue();
});

it('keeps money away from operations and operations detail away from finance', function (): void {
    $ops = userWithRole(Roles::OPERATIONS);
    $finance = userWithRole(Roles::FINANCE);

    expect($ops->can('finance.view-amounts'))->toBeFalse()
        ->and($ops->can('invoices.view'))->toBeFalse()
        ->and($ops->can('crew.update'))->toBeTrue();

    expect($finance->can('finance.view-amounts'))->toBeTrue()
        ->and($finance->can('invoices.issue'))->toBeTrue()
        ->and($finance->can('crew.update'))->toBeFalse();
});

it('lets only finance grant operational release', function (): void {
    expect(userWithRole(Roles::FINANCE)->can('bookings.release-operations'))->toBeTrue()
        ->and(userWithRole(Roles::SALES)->can('bookings.release-operations'))->toBeFalse()
        ->and(userWithRole(Roles::OPERATIONS)->can('bookings.release-operations'))->toBeFalse();
});

it('lets only admin override a gate', function (string $role, bool $expected): void {
    expect(userWithRole($role)->can('gates.override'))->toBe($expected);
})->with([
    [Roles::SALES, false],
    [Roles::OPERATIONS, false],
    [Roles::FINANCE, false],
    [Roles::ADMIN, true],
]);

it('withholds VIP field access from every role by default', function (string $role): void {
    // clients.view-vip is granted per user, never wholesale by role (Q3).
    expect(userWithRole($role)->can('clients.view-vip'))->toBeFalse();
})->with([Roles::SALES, Roles::OPERATIONS, Roles::FINANCE]);

it('scopes sales to its own records and lets ops and finance see all', function (): void {
    expect(userWithRole(Roles::SALES)->can('records.view-all'))->toBeFalse()
        ->and(userWithRole(Roles::OPERATIONS)->can('records.view-all'))->toBeTrue()
        ->and(userWithRole(Roles::FINANCE)->can('records.view-all'))->toBeTrue();
});

it('gives every writable entity a complete CRUD permission set', function (): void {
    $missing = [];

    foreach (Permissions::entities() as $entity) {
        foreach (Permissions::CRUD as $verb) {
            if (! Permission::where('name', "{$entity}.{$verb}")->exists()) {
                $missing[] = "{$entity}.{$verb}";
            }
        }
    }

    expect($missing)->toBe([]);
});

it('gives ledger tables read access only', function (string $entity): void {
    expect(Permission::where('name', "{$entity}.view")->exists())->toBeTrue()
        ->and(Permission::where('name', "{$entity}.create")->exists())->toBeFalse()
        ->and(Permission::where('name', "{$entity}.update")->exists())->toBeFalse()
        ->and(Permission::where('name', "{$entity}.delete")->exists())->toBeFalse();
})->with(Permissions::READ_ONLY);
