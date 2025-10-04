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
    }
}