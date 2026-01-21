<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampaignsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->info('No categories found, skipping campaigns seeding.');
            return;
        }

        $campaigns = [
            [
                'title' => 'Urgence Grand Froid',
                'description' => 'Aidez les familles vulnérables à survivre à l\'hiver rigoureux. Nous distribuons des kits hiver (couvertures, chauffages)',
                'goal_amount' => 50000,
                'target_slug' => 'emergency-relief',
            ],
            [
                'title' => 'Soins Médicaux pour Orphelins',
                'description' => 'Financement des opérations chirurgicales et soins médicaux essentiels pour les orphelins atteints de maladies chroniques',
                'goal_amount' => 120000,
                'target_slug' => 'healthcare',
            ],
            [
                'title' => 'Rentrée Scolaire Solidaire',
                'description' => 'Distribution de cartables et fournitures scolaires pour assurer la scolarité de 500 orphelins',
                'goal_amount' => 75000,
                'target_slug' => 'education',
            ],
            [
                'title' => 'Panier Alimentaire Ramadan',
                'description' => 'Distribution de colis alimentaires pour les familles nécessiteuses durant le mois sacré',
                'goal_amount' => 200000,
                'target_slug' => 'seasonal-relief',
            ],
            [
                'title' => 'Rénovation d\'Habitat Insalubre',
                'description' => 'Aide à la rénovation des logements précaires pour 10 familles de veuves',
                'goal_amount' => 150000,
                'target_slug' => 'emergency-relief',
            ],
        ];

        foreach ($campaigns as $data) {
            $category = $categories->where('slug', $data['target_slug'])->first() 
                ?? $categories->random();

            $currentAmount = rand(0, $data['goal_amount'] * 0.8); // 0 to 80% funded
            $startDate = now()->subDays(rand(1, 60));
            $endDate = now()->addDays(rand(15, 90));

            Campaign::create([
                'category_id' => $category->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'],
                'goal_amount' => $data['goal_amount'],
                'current_amount' => $currentAmount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'urgency' => rand(0, 1) ? 'urgent' : 'normal',
                'image' => null, // Placeholder or specific image logic could go here
                'beneficiaries_count' => rand(50, 500),
                'is_featured' => rand(0, 1) == 1,
            ]);
        }
    }
}
