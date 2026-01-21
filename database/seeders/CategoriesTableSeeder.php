<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Emergency Relief',
                'slug' => 'emergency-relief',
                'description' => 'Aide d\'urgence pour les familles en situation critique',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Healthcare',
                'slug' => 'healthcare',
                'description' => 'Services de santé et soins médicaux pour veuves et orphelins',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Support éducatif et fournitures scolaires',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Seasonal Relief',
                'slug' => 'seasonal-relief',
                'description' => 'Aide saisonnière (vêtements, nourriture, etc.)',
                'color' => '#10B981',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}