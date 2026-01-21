<?php

namespace Database\Seeders;

use App\Models\Orphan;
use App\Models\Sponsorship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SponsorshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        // Get all sponsors (users with role 'sponsor')
        $sponsors = User::where('role', 'sponsor')->get();
        
        // Get all orphans
        $orphans = Orphan::all();
        
        // If no sponsors or orphans, return
        if ($sponsors->isEmpty() || $orphans->isEmpty()) {
            $this->command->info('No sponsors or orphans found. Please seed users and orphans first.');
            return;
        }
        
        // Status weights (higher number = more likely)
        $statusWeights = [
            'active' => 70,    // 70% chance
            'paused' => 10,    // 10% chance
            'completed' => 15, // 15% chance
            'cancelled' => 5   // 5% chance
        ];
        
        // Payment frequency weights
        $frequencyWeights = [
            'monthly' => 70,   // 70% chance
            'quarterly' => 20, // 20% chance
            'yearly' => 10     // 10% chance
        ];
        
        // Create between 20 and 50 sponsorships
        $count = $faker->numberBetween(20, 50);
        
        for ($i = 0; $i < $count; $i++) {
            // Get random sponsor and orphan
            $sponsor = $sponsors->random();
            $orphan = $orphans->random();
            
            // Determine sponsorship period
            $startDate = $faker->dateTimeBetween('-2 years', 'now');
            $endDate = $faker->optional(0.3, null)->dateTimeBetween($startDate, '+2 years');
            
            // Determine status based on dates
            $status = $this->determineStatus($startDate, $endDate, $statusWeights);
            
            // If status is completed or cancelled, ensure end date is in the past
            if (in_array($status, ['completed', 'cancelled']) && $endDate > new \DateTime()) {
                $endDate = $faker->dateTimeBetween($startDate, 'now');
            }
            
            // Create the sponsorship
            Sponsorship::create([
                'user_id' => $sponsor->id,
                'family_id' => $orphan->family_id,
                'orphan_id' => $faker->boolean(100) ? $orphan->id : null, // 80% chance to assign to this specific orphan
                'monthly_amount' => $faker->randomElement([100, 150, 200, 250, 300, 400, 500]),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'payment_frequency' => $this->getWeightedRandom($frequencyWeights),
                'notes' => $faker->optional(0.7)->sentence(), // 70% chance to have notes
                'created_at' => $startDate,
                'updated_at' => $faker->dateTimeBetween($startDate, 'now'),
            ]);
        }
        
        $this->command->info("Created {$count} sponsorships.");
    }
    
    /**
     * Determine status based on dates and weights
     */
    private function determineStatus($startDate, $endDate, $statusWeights)
    {
        $now = new \DateTime();
        
        // If end date is in the past, it's either completed or cancelled
        if ($endDate && $endDate < $now) {
            return $this->getWeightedRandom([
                'completed' => $statusWeights['completed'],
                'cancelled' => $statusWeights['cancelled']
            ]);
        }
        
        // If start date is in the future, it's pending
        if ($startDate > $now) {
            return 'pending';
        }
        
        // Otherwise, it's either active or paused
        return $this->getWeightedRandom([
            'active' => $statusWeights['active'],
            'paused' => $statusWeights['paused']
        ]);
    }
    
    /**
     * Get a random key from an array where the values are weights
     */
    private function getWeightedRandom(array $weightedValues)
    {
        $rand = mt_rand(1, (int) array_sum($weightedValues));
        
        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weightedValues);
    }
}