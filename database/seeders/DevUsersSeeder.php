<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local accounts, one per role, so the permission matrix can be walked through
 * by hand. Two-factor enrolment is still required on first sign-in — the
 * mandate has no exceptions, including for seeded users.
 */
class DevUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Walidia!Harbour2026');

        $users = [
            ['name' => 'Amal Haddad', 'email' => 'sales@walidia.test', 'role' => Roles::SALES, 'job_title' => 'Charter Sales Manager'],
            ['name' => 'Rami Khoury', 'email' => 'operations@walidia.test', 'role' => Roles::OPERATIONS, 'job_title' => 'Operations Manager'],
            ['name' => 'Noor Rahman', 'email' => 'finance@walidia.test', 'role' => Roles::FINANCE, 'job_title' => 'Finance Controller'],
            ['name' => 'Dana Al Falasi', 'email' => 'admin@walidia.test', 'role' => Roles::ADMIN, 'job_title' => 'Managing Director'],
        ];

        $created = [];

        foreach ($users as $attributes) {
            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                [
                    'name' => $attributes['name'],
                    'password' => $password,
                    'job_title' => $attributes['job_title'],
                    'locale' => 'en',
                    'timezone' => config('walidia.display_timezone'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$attributes['role']]);
            $created[$attributes['role']] = $user;
        }

        $team = Team::updateOrCreate(
            ['name' => 'Charter Sales'],
            ['business_line' => 'charter', 'lead_user_id' => $created[Roles::ADMIN]->id, 'is_active' => true],
        );

        $team->members()->syncWithoutDetaching([
            $created[Roles::SALES]->id => ['role_in_team' => 'member'],
            $created[Roles::ADMIN]->id => ['role_in_team' => 'lead'],
        ]);

        $this->command->info('Seeded 4 users — password: Walidia!Harbour2026');
    }
}
