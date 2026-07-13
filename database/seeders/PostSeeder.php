<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'vertus-prise-en-charge-orphelins-islam',
                'title' => [
                    'fr' => 'Les vertus de la prise en charge des orphelins en Islam',
                    'en' => 'The virtues of caring for orphans in Islam',
                ],
                'excerpt' => [
                    'fr' => 'Le Prophète Muhammad (PSL) a dit : « Moi et celui qui prend en charge un orphelin serons comme ces deux-là au Paradis », montrant son index et son majeur.',
                    'en' => 'The Prophet Muhammad (PBUH) said: "I and the one who cares for an orphan will be like these two in Paradise", pointing to his index and middle fingers.',
                ],
                'content' => [
                    'fr' => '<p>La prise en charge des orphelins occupe une place spéciale en Islam. Le Prophète Muhammad (PSL) l\'a souligné dans de nombreux hadiths.</p><p>Le Coran mentionne les orphelins dans de nombreux versets, nous rappelant notre devoir de les protéger et de les élever. Dans la sourate Al-Baqarah, Allah dit : « Ils t\'interrogent au sujet des orphelins. Dis : La meilleure chose est ce qui est dans leur intérêt. »</p><p>Parrainer un orphelin n\'est pas simplement un acte de charité — c\'est un investissement pour l\'Au-delà. Le Prophète (PSL) a dit : « Moi et celui qui prend en charge un orphelin serons comme ces deux-là au Paradis », levant son index et son majeur ensemble.</p><p>Chez Al-Aytaam, nous veillons à ce que chaque enfant parrainé reçoive éducation, soins médicaux, nutrition et soutien émotionnel. Votre contribution transforme directement la vie d\'un enfant.</p>',
                    'en' => '<p>Caring for orphans holds a special place in Islam. The Prophet Muhammad (PBUH) emphasized it in numerous hadiths.</p><p>The Quran mentions orphans in many verses, reminding us of our duty to protect and raise them. In Surah Al-Baqarah, Allah says: "They ask you about orphans. Say: The best thing is what is in their interest."</p><p>Sponsoring an orphan is not merely an act of charity — it is an investment for the Hereafter. The Prophet (PBUH) said: "I and the one who cares for an orphan will be like these two in Paradise", raising his index and middle fingers together.</p><p>At Al-Aytaam, we make sure every sponsored child receives education, medical care, nutrition and emotional support. Your contribution directly transforms a child\'s life.</p>',
                ],
                'author' => 'Cheikh Ahmad Al-Rashidi',
                'category' => ['fr' => 'Enseignements islamiques', 'en' => 'Islamic teachings'],
                'read_time' => '5 min',
                'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c8df?w=600',
                'published_at' => '2026-02-10',
            ],
            [
                'slug' => 'vos-dons-ont-change-500-vies',
                'title' => [
                    'fr' => 'Comment vos dons ont changé 500 vies l\'année dernière',
                    'en' => 'How your donations changed 500 lives last year',
                ],
                'excerpt' => [
                    'fr' => 'Un rapport annuel sur l\'impact de vos généreux dons dans 12 pays où nous servons orphelins, veuves et familles dans le besoin.',
                    'en' => 'An annual report on the impact of your generous donations in 12 countries where we serve orphans, widows and families in need.',
                ],
                'content' => [
                    'fr' => '<p>Alhamdoulillah, l\'année dernière a été notre année la plus impactante. Grâce à votre généreux soutien, nous avons pu atteindre plus de 500 bénéficiaires dans 12 pays.</p><h3>Réalisations clés</h3><ul><li>320 orphelins ont reçu un parrainage continu</li><li>85 veuves ont bénéficié d\'allocations mensuelles</li><li>95 familles ont reçu une aide d\'urgence et continue</li><li>12 nouvelles écoles ont été construites</li><li>3 cliniques médicales ont été établies</li></ul>',
                    'en' => '<p>Alhamdulillah, last year was our most impactful year. Thanks to your generous support, we were able to reach more than 500 beneficiaries in 12 countries.</p><h3>Key achievements</h3><ul><li>320 orphans received ongoing sponsorship</li><li>85 widows received monthly allowances</li><li>95 families received emergency and ongoing aid</li><li>12 new schools were built</li><li>3 medical clinics were established</li></ul>',
                ],
                'author' => 'Amina Hassan',
                'category' => ['fr' => 'Histoires d\'impact', 'en' => 'Impact stories'],
                'read_time' => '7 min',
                'image' => 'https://images.unsplash.com/photo-1497375638960-ca368c7231e4?w=600',
                'published_at' => '2026-01-25',
            ],
            [
                'slug' => 'autonomiser-les-veuves-formation-professionnelle',
                'title' => [
                    'fr' => 'Autonomiser les veuves par la formation professionnelle',
                    'en' => 'Empowering widows through vocational training',
                ],
                'excerpt' => [
                    'fr' => 'Notre nouvelle initiative offre une formation professionnelle aux veuves, les aidant à devenir autonomes et à construire un meilleur avenir pour leurs familles.',
                    'en' => 'Our new initiative offers vocational training to widows, helping them become self-sufficient and build a better future for their families.',
                ],
                'content' => [
                    'fr' => '<p>Notre dernière initiative se concentre sur l\'autonomisation des veuves grâce à des programmes complets de formation professionnelle.</p><h3>Le programme comprend</h3><ul><li>Ateliers de couture et confection</li><li>Cours de gestion de petites entreprises</li><li>Cours d\'alphabétisation et de calcul</li><li>Programmes de micro-crédit pour lancer des entreprises</li></ul><p>Jusqu\'à présent, 45 veuves ont terminé le programme, dont 30 gèrent déjà leur propre petite entreprise.</p>',
                    'en' => '<p>Our latest initiative focuses on empowering widows through comprehensive vocational training programs.</p><h3>The program includes</h3><ul><li>Sewing and tailoring workshops</li><li>Small business management courses</li><li>Literacy and numeracy classes</li><li>Micro-credit programs to launch businesses</li></ul><p>So far, 45 widows have completed the program, 30 of whom are already running their own small businesses.</p>',
                ],
                'author' => 'Dr. Maryam Qureshi',
                'category' => ['fr' => 'Programmes', 'en' => 'Programs'],
                'read_time' => '6 min',
                'image' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=600',
                'published_at' => '2026-02-01',
            ],
            [
                'slug' => 'ramadan-mois-de-generosite',
                'title' => [
                    'fr' => 'Ramadan : un mois de générosité et de compassion',
                    'en' => 'Ramadan: a month of generosity and compassion',
                ],
                'excerpt' => [
                    'fr' => 'À l\'approche du Ramadan, découvrez comment maximiser vos dons caritatifs et multiplier vos récompenses durant ce mois béni.',
                    'en' => 'As Ramadan approaches, discover how to maximize your charitable giving and multiply your rewards during this blessed month.',
                ],
                'content' => [
                    'fr' => '<p>Le Ramadan est le mois de la générosité, de la compassion et de l\'adoration accrue. C\'est un moment où les récompenses des bonnes actions sont multipliées.</p><h3>Comment maximiser vos dons pendant le Ramadan</h3><ol><li>Mettre en place des dons récurrents</li><li>Parrainer les repas d\'iftar d\'un orphelin</li><li>Contribuer à notre fonds Zakat</li><li>Partager notre mission avec vos proches</li></ol>',
                    'en' => '<p>Ramadan is the month of generosity, compassion and increased worship. It is a time when the rewards of good deeds are multiplied.</p><h3>How to maximize your giving during Ramadan</h3><ol><li>Set up recurring donations</li><li>Sponsor an orphan\'s iftar meals</li><li>Contribute to our Zakat fund</li><li>Share our mission with your loved ones</li></ol>',
                ],
                'author' => 'Imam Khalid Mahmoud',
                'category' => ['fr' => 'Saisonnier', 'en' => 'Seasonal'],
                'read_time' => '4 min',
                'image' => 'https://images.unsplash.com/photo-1564769625905-50e93615e769?w=600',
                'published_at' => '2026-02-08',
            ],
        ];

        foreach ($posts as $data) {
            Post::updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'status' => 'published']
            );
        }
    }
}
