<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kreiramo role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $techRole  = Role::firstOrCreate(['name' => 'technician']);
        $workerRole = Role::firstOrCreate(['name' => 'worker']);

        // Milan
        $milan = User::updateOrCreate(
            ['username' => 'milan'],
            [
                'name'     => 'Milan',
                'email'    => 'milan@karting-hq.test',
                'password' => Hash::make('milan123'),
            ]
        );
        $milan->syncRoles([$adminRole]);

        // Djordje
        $djordje = User::updateOrCreate(
            ['username' => 'djordje'],
            [
                'name'     => 'Djordje',
                'email'    => 'djordje@karting-hq.test',
                'password' => Hash::make('djordje123'),
            ]
        );
        $djordje->syncRoles([$adminRole]);
    }
}
