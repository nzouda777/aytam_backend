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
                'name' => ['fr' => 'Aide d\'urgence', 'en' => 'Emergency Relief'],
                'slug' => 'emergency-relief',
                'description' => [
                    'fr' => 'Aide d\'urgence pour les familles en situation critique',
                    'en' => 'Emergency relief for families in critical situations',
                ],
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => ['fr' => 'Santé', 'en' => 'Healthcare'],
                'slug' => 'healthcare',
                'description' => [
                    'fr' => 'Services de santé et soins médicaux pour veuves et orphelins',
                    'en' => 'Health services and medical care for widows and orphans',
                ],
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => ['fr' => 'Éducation', 'en' => 'Education'],
                'slug' => 'education',
                'description' => [
                    'fr' => 'Support éducatif et fournitures scolaires',
                    'en' => 'Educational support and school supplies',
                ],
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => ['fr' => 'Aide saisonnière', 'en' => 'Seasonal Relief'],
                'slug' => 'seasonal-relief',
                'description' => [
                    'fr' => 'Aide saisonnière (vêtements, nourriture, etc.)',
                    'en' => 'Seasonal aid (clothing, food, etc.)',
                ],
                'color' => '#10B981',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
