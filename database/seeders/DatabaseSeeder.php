<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            FamilySeeder::class,
            OrphanSeeder::class,
            SponsorshipSeeder::class, // Add this line
        ]);
    }
}