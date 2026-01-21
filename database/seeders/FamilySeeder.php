<?php

namespace Database\Seeders;

use App\Models\Family;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = ['Casablanca', 'Rabat', 'Fes', 'Marrakech', 'Tangier', 'Agadir', 'Meknes', 'Oujda', 'Kenitra', 'Tetouan'];
        $needs = [
            'Nourriture, Vêtements, Scolarité',
            'Loyer, Électricité, Eau',
            'Soins médicaux, Médicaments',
            'Frais scolaires, Fournitures',
            'Nourriture, Loyer, Soins médicaux'
        ];
        
        for ($i = 1; $i <= 20; $i++) {
            $orphansCount = rand(1, 5);
            $statuses = ['active', 'inactive', 'pending'];
            $status = $statuses[array_rand($statuses)];
            $registrationDate = now()->subMonths(rand(1, 12))->subDays(rand(1, 30));
            
            Family::create([
                'family_code' => 'FAM-' . strtoupper(Str::random(8)),
                'widow_name' => 'Fatima ' . $this->getRandomLastName(),
                'widow_phone' => '06' . rand(10000000, 99999999),
                'widow_email' => 'famille' . $i . '@example.com',
                'widow_date_of_birth' => now()->subYears(rand(25, 55))->subMonths(rand(1, 12))->format('Y-m-d'),
                'address' => 'Quartier ' . ['Al Qods', 'Al Falah', 'Al Massira', 'Hay Nahda', 'Hay Salam'][array_rand([0, 1, 2, 3, 4])] . ', Rue ' . rand(1, 100),
                'city' => $cities[array_rand($cities)],
                'region' => ['Casablanca-Settat', 'Rabat-Salé-Kénitra', 'Fès-Meknès', 'Marrakech-Safi', 'Tanger-Tétouan-Al Hoceïma'][array_rand([0, 1, 2, 3, 4])],
                'orphans_count' => $orphansCount,
                'needs' => $needs[array_rand($needs)],
                'status' => $status,
                'registration_date' => $registrationDate->format('Y-m-d'),
                'notes' => $this->getRandomNote($status),
                'total_needs' => $orphansCount * rand(1000, 3000),
                'total_received' => $orphansCount * rand(500, 2500),
                'created_at' => $registrationDate,
                'updated_at' => $registrationDate->copy()->addDays(rand(1, 30)),
            ]);
        }
    }

    private function getRandomLastName(): string
    {
        $lastNames = [
            'Alaoui', 'Benjelloun', 'Cherkaoui', 'El Fassi', 'El Khayat',
            'El Mansouri', 'El Ouazzani', 'Hassani', 'Idrissi', 'Jabri',
            'Kabbaj', 'Lahlou', 'Mansouri', 'Naciri', 'Oufkir',
            'Rahmouni', 'Saadi', 'Tazi', 'Zerouali', 'Zouhair'
        ];
        
        return $lastNames[array_rand($lastNames)];
    }

    private function getRandomNote(string $status): string
    {
        $notes = [
            'active' => [
                'Famille suivie régulièrement',
                'Bons progrès dans la situation',
                'Enfants scolarisés avec succès',
                'Situation stable',
                'Bénéficie d\'un parrainage actif'
            ],
            'inactive' => [
                'Plus de contact depuis plusieurs mois',
                'Déménagement sans laisser d\'adresse',
                'Situation financière améliorée',
                'A demandé à arrêter le suivi',
                'Enfants majeurs et indépendants'
            ],
            'pending' => [
                'Nouvelle demande en cours d\'évaluation',
                'En attente de documents complémentaires',
                'En cours de visite à domicile',
                'En attente de validation du comité',
                'Dossier en cours d\'instruction'
            ]
        ];
        
        return $notes[$status][array_rand($notes[$status])];
    }
}
