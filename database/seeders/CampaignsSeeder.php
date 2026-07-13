<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Database\Seeder;

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
                'title' => [
                    'fr' => 'Urgence Grand Froid',
                    'en' => 'Winter Emergency Appeal',
                ],
                'description' => [
                    'fr' => 'Aidez les familles vulnérables à survivre à l\'hiver rigoureux. Nous distribuons des kits hiver (couvertures, chauffages)',
                    'en' => 'Help vulnerable families survive the harsh winter. We distribute winter kits (blankets, heaters)',
                ],
                'slug' => 'urgence-grand-froid',
                'goal_amount' => 50000,
                'target_slug' => 'emergency-relief',
            ],
            [
                'title' => [
                    'fr' => 'Soins Médicaux pour Orphelins',
                    'en' => 'Medical Care for Orphans',
                ],
                'description' => [
                    'fr' => 'Financement des opérations chirurgicales et soins médicaux essentiels pour les orphelins atteints de maladies chroniques',
                    'en' => 'Funding for surgeries and essential medical care for orphans suffering from chronic illnesses',
                ],
                'slug' => 'soins-medicaux-pour-orphelins',
                'goal_amount' => 120000,
                'target_slug' => 'healthcare',
            ],
            [
                'title' => [
                    'fr' => 'Rentrée Scolaire Solidaire',
                    'en' => 'Back-to-School Drive',
                ],
                'description' => [
                    'fr' => 'Distribution de cartables et fournitures scolaires pour assurer la scolarité de 500 orphelins',
                    'en' => 'Distribution of school bags and supplies to keep 500 orphans in school',
                ],
                'slug' => 'rentree-scolaire-solidaire',
                'goal_amount' => 75000,
                'target_slug' => 'education',
            ],
            [
                'title' => [
                    'fr' => 'Panier Alimentaire Ramadan',
                    'en' => 'Ramadan Food Basket',
                ],
                'description' => [
                    'fr' => 'Distribution de colis alimentaires pour les familles nécessiteuses durant le mois sacré',
                    'en' => 'Distribution of food parcels to families in need during the holy month',
                ],
                'slug' => 'panier-alimentaire-ramadan',
                'goal_amount' => 200000,
                'target_slug' => 'seasonal-relief',
            ],
            [
                'title' => [
                    'fr' => 'Rénovation d\'Habitat Insalubre',
                    'en' => 'Housing Renovation Program',
                ],
                'description' => [
                    'fr' => 'Aide à la rénovation des logements précaires pour 10 familles de veuves',
                    'en' => 'Help renovate substandard housing for 10 widows\' families',
                ],
                'slug' => 'renovation-dhabitat-insalubre',
                'goal_amount' => 150000,
                'target_slug' => 'emergency-relief',
            ],
        ];

        foreach ($campaigns as $data) {
            $category = $categories->where('slug', $data['target_slug'])->first()
                ?? $categories->random();

            // Les champs aléatoires ne sont générés qu'à la création :
            // relancer le seeder met à jour les traductions sans écraser
            // l'avancement d'une campagne existante.
            $campaign = Campaign::where('slug', $data['slug'])->first();

            if ($campaign) {
                $campaign->update([
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                continue;
            }

            Campaign::create([
                'category_id' => $category->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'goal_amount' => $data['goal_amount'],
                'current_amount' => rand(0, $data['goal_amount'] * 0.8),
                'start_date' => now()->subDays(rand(1, 60)),
                'end_date' => now()->addDays(rand(15, 90)),
                'status' => 'active',
                'urgency' => rand(0, 1) ? 'urgent' : 'normal',
                'image' => null,
                'beneficiaries_count' => rand(50, 500),
                'is_featured' => rand(0, 1) == 1,
            ]);
        }
    }
}
