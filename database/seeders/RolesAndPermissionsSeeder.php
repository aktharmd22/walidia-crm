<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent: safe to re-run after every phase adds permissions.
 * Permissions removed from the catalogue are removed from roles too, so the
 * database never drifts from App\Support\Permissions.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $names = Permissions::all();
        $now = now();

        // One bulk upsert rather than 700 round trips: this seeder runs in
        // every feature test, so its cost is paid on every build.
        Permission::query()->upsert(
            array_map(
                fn (string $name): array => [
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $names,
            ),
            ['name', 'guard_name'],
            ['updated_at'],
        );

        Permission::query()->whereNotIn('name', $names)->delete();

        foreach (Roles::all() as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(Roles::permissionsFor($roleName));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info(sprintf(
            'Seeded %d permissions across %d roles.',
            count($names),
            count(Roles::all()),
        ));
    }
}
