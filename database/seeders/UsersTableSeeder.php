<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        User::create([
            'name' => 'Admin Yateem',
            'email' => 'admin@yateem.org',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+237690000000',
            'address' => 'Douala, Cameroun',
            'is_active' => true,
        ]);

        // Manager
        User::create([
            'name' => 'Manager Yateem',
            'email' => 'manager@yateem.org',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '+237690000001',
            'address' => 'Douala, Cameroun',
            'is_active' => true,
        ]);

        // Quelques donateurs de test
        User::create([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => Hash::make('password'),
            'role' => 'donor',
            'phone' => '+237690000002',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Marie Martin',
            'email' => 'marie.martin@example.com',
            'password' => Hash::make('password'),
            'role' => 'donor',
            'phone' => '+237690000003',
            'is_active' => true,
        ]);

        // Quelques parrains de test
        User::create([
            'name' => 'Pierre Bernard',
            'email' => 'pierre.bernard@example.com',
            'password' => Hash::make('password'),
            'role' => 'sponsor',
            'phone' => '+237690000004',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Sophie Laurent',
            'email' => 'sophie.laurent@example.com',
            'password' => Hash::make('password'),
            'role' => 'sponsor',
            'phone' => '+237690000005',
            'is_active' => true,
        ]);

        // Un autre parrain avec plus d'informations
        User::create([
            'name' => 'Claire Moreau',
            'email' => 'claire.moreau@example.com',
            'password' => Hash::make('password'),
            'role' => 'sponsor',
            'phone' => '+237690000006',
            'address' => 'Yaoundé, Cameroun',
            'is_active' => true,
        ]);

    }
}