<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Superadmin User
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Superadmin WABlast',
                'email' => 'superadmin@wablast.com',
                'role' => 'superadmin',
                'wa_instance_name' => 'superadmin_wa',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Admin User
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin WABlast',
                'email' => 'admin@wablast.com',
                'role' => 'admin',
                'wa_instance_name' => 'admin_wa',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Regular Pengguna User
        User::updateOrCreate(
            ['username' => 'pengguna'],
            [
                'name' => 'Pengguna WABlast',
                'email' => 'user@wablast.com',
                'role' => 'pengguna',
                'wa_instance_name' => 'pengguna_wa',
                'password' => Hash::make('password'),
            ]
        );
    }
}
