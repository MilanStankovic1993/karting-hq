<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $teamA = Team::firstOrCreate(['name' => 'Team Alpha']);
        $teamB = Team::firstOrCreate(['name' => 'Team Bravo']);

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@karting-hq.com');
        $adminPass  = env('SEED_ADMIN_PASSWORD', 'ChangeMe123!');

        // SUPER ADMIN
        User::updateOrCreate(
            ['username' => env('SEED_ADMIN_USERNAME', 'milan')],
            [
                'name'      => env('SEED_ADMIN_NAME', 'Milan'),
                'email'     => $adminEmail,
                'password'  => Hash::make($adminPass),
                'role'      => User::ROLE_SUPER_ADMIN,
                'team_id'   => null,
                'is_active' => true, // ✅
            ]
        );

        // TECHNICIAN Team Alpha
        User::updateOrCreate(
            ['username' => 'djordje'],
            [
                'name'      => 'Djordje',
                'email'     => 'djordje@karting-hq.com',
                'password'  => Hash::make('djordje123'),
                'role'      => User::ROLE_TECHNICIAN,
                'team_id'   => $teamA->id,
                'is_active' => true, // ✅
            ]
        );

        // WORKER Team Alpha
        User::updateOrCreate(
            ['username' => 'radnik1'],
            [
                'name'      => 'Radnik 1',
                'email'     => 'radnik1@karting-hq.com',
                'password'  => Hash::make('radnik123'),
                'role'      => User::ROLE_WORKER,
                'team_id'   => $teamA->id,
                'is_active' => true, // ✅
            ]
        );

        // TECHNICIAN Team Bravo
        User::updateOrCreate(
            ['username' => 'tehnicar2'],
            [
                'name'      => 'Tehnicar 2',
                'email'     => 'tehnicar2@karting-hq.com',
                'password'  => Hash::make('tehnicar2123'),
                'role'      => User::ROLE_TECHNICIAN,
                'team_id'   => $teamB->id,
                'is_active' => true, // ✅
            ]
        );
    }
}
