<?php

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Orphan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class OrphanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR'); // Using French locale for Moroccan names
        $faker->addProvider(new \Faker\Provider\fr_FR\Person($faker));
        $faker->addProvider(new \Faker\Provider\fr_FR\Address($faker));
        $faker->addProvider(new \Faker\Provider\fr_FR\PhoneNumber($faker));
        $faker->addProvider(new \Faker\Provider\fr_FR\Company($faker));

        // Get all families to associate with orphans
        $families = Family::all();
        
        // Common Moroccan last names
        $lastNames = [
            'Alaoui', 'Benjelloun', 'Cherkaoui', 'El Fassi', 'El Khayat', 
            'El Mansouri', 'El Ouazzani', 'Hassani', 'Idrissi', 'Jabri',
            'Kabbaj', 'Lahlou', 'Mansouri', 'Naciri', 'Oufkir',
            'Rahmouni', 'Saadi', 'Tazi', 'Zerouali', 'Zouhair',
            'Bennani', 'Chraibi', 'Daoudi', 'El Amrani', 'El Filali'
        ];
        
        // School information
        $schools = [
            'Groupe Scolaire Al Jabr', 'Lycée Descartes', 'École Al Madina', 
            'Lycée Lyautey', 'Groupe Scolaire La Résidence', 'École Al Akhawayn', 
            'Lycée Victor Hugo', 'Groupe Scolaire Al Qalam', 'École Al Moustaqbal', 
            'Lycée Paul Valéry'
        ];
        
        $schoolLevels = [
            'Préscolaire', 'Primaire CP', 'Primaire CE1', 'Primaire CE2', 
            'Primaire CM1', 'Primaire CM2', 'Collège 1ère année', 
            'Collège 2ème année', 'Collège 3ème année', 'Collège 4ème année',
            'Lycée 1ère année', 'Lycée 2ème année', 'Lycée 3ème année'
        ];
        
        // Create orphans for each family
        foreach ($families as $family) {
            // Determine number of orphans for this family (between 1 and 5)
            $orphanCount = $family->orphans_count ?? $faker->numberBetween(1, 5);
            $createdAt = $family->created_at ?? now();
            
            for ($i = 0; $i < $orphanCount; $i++) {
                $gender = $faker->randomElement(['male', 'female']);
                $firstName = $faker->firstName($gender);
                
                // Calculate birth date (between 4 and 18 years ago)
                $age = $faker->numberBetween(4, 18);
                $birthDate = now()->subYears($age)
                                ->subMonths($faker->numberBetween(0, 11))
                                ->subDays($faker->numberBetween(0, 30));
                
                // School information
                $isSchoolAge = $age >= 6 && $age <= 18;
                $schoolName = $isSchoolAge && $faker->boolean(90) 
                    ? $faker->randomElement($schools) 
                    : null;
                $schoolLevel = $schoolName ? $this->getSchoolLevel($age) : null;
                
                // Health and special needs
                $hasSpecialNeeds = $faker->boolean(20); // 20% chance of special needs
                $healthStatus = $hasSpecialNeeds 
                    ? $faker->randomElement([
                        'Nécessite des soins réguliers',
                        'Problèmes de vue',
                        'Problèmes auditifs',
                        'Maladie chronique contrôlée',
                        'Besoins spécifiques en orthophonie'
                    ])
                    : 'Bon état de santé';
                    
                $specialNeed = $hasSpecialNeeds 
                    ? $faker->randomElement([
                        'Besoins éducatifs particuliers',
                        'Accompagnement psychologique',
                        'Soutien scolaire renforcé',
                        'Suivi médical régulier',
                        'Appareillage auditif',
                        'Lunettes de vue'
                    ])
                    : null;
                
                // Create the orphan
                Orphan::create([
                    'family_id' => $family->id,
                    'first_name' => $firstName,
                    'last_name' => $family->last_name ?? $faker->randomElement($lastNames),
                    'date_of_birth' => $birthDate->format('Y-m-d'),
                    'gender' => $gender,
                    'school_name' => $schoolName,
                    'school_level' => $schoolLevel,
                    'health_status' => $healthStatus,
                    'special_needs' => $specialNeed,
                    'is_sponsored' => $faker->boolean(30), // 30% chance of being sponsored
                    'created_at' => $createdAt,
                    'updated_at' => (clone $createdAt)->addDays($faker->numberBetween(1, 30)),
                ]);
            }
            
            // Update the family's orphan count to match the actual number of orphans created
            $family->update(['orphans_count' => $orphanCount]);
        }
    }
    
    /**
     * Determine school level based on age
     */
    private function getSchoolLevel(int $age): string
    {
        $levels = [
            6 => 'Primaire CP',
            7 => 'Primaire CE1',
            8 => 'Primaire CE2',
            9 => 'Primaire CM1',
            10 => 'Primaire CM2',
            11 => 'Collège 1ère année',
            12 => 'Collège 2ème année',
            13 => 'Collège 3ème année',
            14 => 'Collège 4ème année',
            15 => 'Lycée 1ère année',
            16 => 'Lycée 2ème année',
            17 => 'Lycée 3ème année',
            18 => 'Lycée 3ème année'
        ];
        
        return $levels[min(max(6, $age), 18)] ?? 'Primaire CP';
    }
}