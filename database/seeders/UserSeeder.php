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
        /*
        |--------------------------------------------------------------------------
        | 1) Kreiramo timove
        |--------------------------------------------------------------------------
        */

        $teamA = Team::firstOrCreate(['name' => 'Team Alpha']);
        $teamB = Team::firstOrCreate(['name' => 'Team Bravo']);

        /*
        |--------------------------------------------------------------------------
        | 2) SUPER ADMIN (nema team_id)
        |--------------------------------------------------------------------------
        */

        $milan = User::updateOrCreate(
            ['username' => 'milan'],
            [
                'name'     => 'Milan',
                'email'    => 'milan@karting-hq.test',
                'password' => Hash::make('milan123'),
                'role'     => User::ROLE_SUPER_ADMIN,
                'team_id'  => null, // SUPER ADMIN nema tim
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 3) TECHNICIAN za Team Alpha
        |--------------------------------------------------------------------------
        */

        $djordje = User::updateOrCreate(
            ['username' => 'djordje'],
            [
                'name'     => 'Djordje',
                'email'    => 'djordje@karting-hq.test',
                'password' => Hash::make('djordje123'),
                'role'     => User::ROLE_TECHNICIAN,
                'team_id'  => $teamA->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 4) WORKER u istom timu kao Đorđe (Team Alpha)
        |--------------------------------------------------------------------------
        */

        $radnik = User::updateOrCreate(
            ['username' => 'radnik1'],
            [
                'name'     => 'Radnik 1',
                'email'    => 'radnik1@karting-hq.test',
                'password' => Hash::make('radnik123'),
                'role'     => User::ROLE_WORKER,
                'team_id'  => $teamA->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 5) Drugi TECHNICIAN u Team Bravo
        |--------------------------------------------------------------------------
        */

        $tech2 = User::updateOrCreate(
            ['username' => 'tehnicar2'],
            [
                'name'     => 'Tehnicar 2',
                'email'    => 'tehnicar2@karting-hq.test',
                'password' => Hash::make('tehnicar2123'),
                'role'     => User::ROLE_TECHNICIAN,
                'team_id'  => $teamB->id,
            ]
        );
    }
}
