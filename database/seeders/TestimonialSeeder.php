<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Ibrahim Al-Farsi',
                'role' => ['fr' => 'Donateur mensuel', 'en' => 'Monthly donor'],
                'quote' => [
                    'fr' => 'Parrainer le petit Ahmed a été l\'expérience la plus enrichissante. Je reçois des nouvelles chaque mois et voir sa progression me remplit de joie.',
                    'en' => 'Sponsoring little Ahmed has been the most rewarding experience. I receive updates every month and watching his progress fills me with joy.',
                ],
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100',
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sarah Thompson',
                'role' => ['fr' => 'Bénévole', 'en' => 'Volunteer'],
                'quote' => [
                    'fr' => 'La transparence et le dévouement d\'Al-Aytaam sont incomparables. Chaque franc atteint vraiment ceux qui en ont besoin. J\'en ai été témoin.',
                    'en' => 'Al-Aytaam\'s transparency and dedication are unmatched. Every franc truly reaches those in need. I have witnessed it firsthand.',
                ],
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100',
                'rating' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => 'Dr. Hassan Malik',
                'role' => ['fr' => 'Organisation partenaire', 'en' => 'Partner organization'],
                'quote' => [
                    'fr' => 'Travailler avec Al-Aytaam a décuplé notre portée. Leur engagement envers la Oummah est vraiment inspirant et leurs systèmes sont professionnels.',
                    'en' => 'Working with Al-Aytaam has multiplied our reach tenfold. Their commitment to the Ummah is truly inspiring and their systems are professional.',
                ],
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100',
                'rating' => 5,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::updateOrCreate(
                ['name' => $data['name']],
                [...$data, 'is_active' => true]
            );
        }
    }
}
