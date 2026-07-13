<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $programs = [
            [
                'slug' => 'orphan-sponsorship',
                'title' => ['fr' => 'Parrainage d\'orphelins', 'en' => 'Orphan sponsorship'],
                'excerpt' => [
                    'fr' => 'Offrez à un orphelin nourriture, éducation, soins médicaux et un avenir digne grâce à un soutien mensuel régulier.',
                    'en' => 'Give an orphan food, education, medical care and a dignified future through regular monthly support.',
                ],
                'content' => [
                    'fr' => '<p>Le parrainage d\'orphelins est le cœur de la mission d\'Al-Aytaam. Chaque enfant parrainé reçoit un soutien mensuel complet couvrant ses besoins essentiels.</p><h3>Ce que couvre votre parrainage</h3><ul><li>Nourriture et nutrition adaptée</li><li>Frais de scolarité et fournitures</li><li>Soins médicaux réguliers</li><li>Suivi psychologique et social</li></ul><p>Vous recevez des nouvelles régulières de votre filleul et pouvez suivre sa progression. Le Prophète ﷺ a dit : « Moi et celui qui prend en charge un orphelin serons comme ces deux-là au Paradis. »</p>',
                    'en' => '<p>Orphan sponsorship is the heart of Al-Aytaam\'s mission. Every sponsored child receives comprehensive monthly support covering their essential needs.</p><h3>What your sponsorship covers</h3><ul><li>Food and proper nutrition</li><li>School fees and supplies</li><li>Regular medical care</li><li>Psychological and social follow-up</li></ul><p>You receive regular updates about your sponsored child and can follow their progress. The Prophet ﷺ said: "I and the one who cares for an orphan will be like these two in Paradise."</p>',
                ],
                'icon' => 'Heart',
                'category_slug' => null,
                'cta_type' => 'sponsor',
                'sort_order' => 1,
            ],
            [
                'slug' => 'widow-empowerment',
                'title' => ['fr' => 'Autonomisation des veuves', 'en' => 'Widow empowerment'],
                'excerpt' => [
                    'fr' => 'Aidez les veuves à subvenir aux besoins de leurs familles grâce à une aide mensuelle et des formations professionnelles.',
                    'en' => 'Help widows provide for their families through monthly aid and vocational training.',
                ],
                'content' => [
                    'fr' => '<p>Les veuves qui élèvent seules leurs enfants font face à d\'immenses difficultés. Notre programme leur apporte un soutien financier et les accompagne vers l\'autonomie.</p><h3>Nos actions</h3><ul><li>Allocations mensuelles pour les besoins essentiels</li><li>Ateliers de couture, confection et petit commerce</li><li>Cours d\'alphabétisation et de gestion</li><li>Micro-crédits pour lancer une activité</li></ul><p>À ce jour, des dizaines de veuves ont terminé nos formations et gèrent leur propre activité génératrice de revenus.</p>',
                    'en' => '<p>Widows raising their children alone face immense hardship. Our program provides financial support and guides them towards self-sufficiency.</p><h3>Our actions</h3><ul><li>Monthly allowances for essential needs</li><li>Sewing, tailoring and small business workshops</li><li>Literacy and management classes</li><li>Micro-credit to launch a business</li></ul><p>To date, dozens of widows have completed our training and run their own income-generating activity.</p>',
                ],
                'icon' => 'HandHeart',
                'category_slug' => null,
                'cta_type' => 'sponsor',
                'sort_order' => 2,
            ],
            [
                'slug' => 'family-support',
                'title' => ['fr' => 'Soutien aux familles', 'en' => 'Family support'],
                'excerpt' => [
                    'fr' => 'Soutenez les familles vulnérables avec logement, nourriture et produits essentiels pour traverser les moments difficiles.',
                    'en' => 'Support vulnerable families with housing, food and essentials to get through difficult times.',
                ],
                'content' => [
                    'fr' => '<p>Nous accompagnons les familles nécessiteuses dans leur ensemble : la stabilité d\'un foyer est la meilleure protection pour les enfants.</p><h3>Nos actions</h3><ul><li>Colis alimentaires mensuels</li><li>Aide au loyer et rénovation de logements insalubres</li><li>Prise en charge des frais de santé</li><li>Parrainage global de la famille</li></ul><p>Votre soutien maintient des familles unies et dignes malgré la précarité.</p>',
                    'en' => '<p>We support families in need as a whole: a stable home is the best protection for children.</p><h3>Our actions</h3><ul><li>Monthly food parcels</li><li>Rent assistance and renovation of substandard housing</li><li>Health costs coverage</li><li>Whole-family sponsorship</li></ul><p>Your support keeps families together and dignified despite hardship.</p>',
                ],
                'icon' => 'Home',
                'category_slug' => 'emergency-relief',
                'cta_type' => 'sponsor',
                'sort_order' => 3,
            ],
            [
                'slug' => 'education-fund',
                'title' => ['fr' => 'Fonds éducation', 'en' => 'Education fund'],
                'excerpt' => [
                    'fr' => 'Construisez des écoles et offrez des bourses aux enfants défavorisés : l\'éducation est la clé d\'un avenir meilleur.',
                    'en' => 'Build schools and provide scholarships to underprivileged children: education is the key to a better future.',
                ],
                'content' => [
                    'fr' => '<p>L\'éducation brise le cycle de la pauvreté. Le fonds éducation d\'Al-Aytaam finance la scolarité des orphelins et des enfants de familles démunies.</p><h3>Nos actions</h3><ul><li>Distribution de cartables et fournitures scolaires</li><li>Prise en charge des frais de scolarité</li><li>Bourses d\'études pour les meilleurs élèves</li><li>Construction et rénovation de salles de classe</li></ul><p>Chaque enfant scolarisé est une génération transformée.</p>',
                    'en' => '<p>Education breaks the cycle of poverty. Al-Aytaam\'s education fund finances schooling for orphans and children from destitute families.</p><h3>Our actions</h3><ul><li>Distribution of school bags and supplies</li><li>School fees coverage</li><li>Scholarships for top students</li><li>Building and renovating classrooms</li></ul><p>Every child in school is a generation transformed.</p>',
                ],
                'icon' => 'GraduationCap',
                'category_slug' => 'education',
                'cta_type' => 'donate',
                'sort_order' => 4,
            ],
            [
                'slug' => 'emergency-relief',
                'title' => ['fr' => 'Aide d\'urgence', 'en' => 'Emergency relief'],
                'excerpt' => [
                    'fr' => 'Apportez une aide immédiate aux familles touchées par les crises : intempéries, maladie, perte de logement.',
                    'en' => 'Provide immediate relief to families hit by crises: severe weather, illness, loss of housing.',
                ],
                'content' => [
                    'fr' => '<p>Quand une crise frappe, chaque heure compte. Notre programme d\'aide d\'urgence intervient rapidement auprès des familles les plus exposées.</p><h3>Nos actions</h3><ul><li>Kits d\'urgence (couvertures, chauffage, hygiène)</li><li>Aide alimentaire immédiate</li><li>Prise en charge médicale d\'urgence</li><li>Relogement temporaire</li></ul><p>Vos dons nous permettent de constituer un fonds de réponse rapide mobilisable à tout moment.</p>',
                    'en' => '<p>When a crisis strikes, every hour counts. Our emergency relief program responds quickly to the most exposed families.</p><h3>Our actions</h3><ul><li>Emergency kits (blankets, heating, hygiene)</li><li>Immediate food aid</li><li>Emergency medical care</li><li>Temporary rehousing</li></ul><p>Your donations allow us to maintain a rapid response fund that can be mobilized at any time.</p>',
                ],
                'icon' => 'Siren',
                'category_slug' => 'emergency-relief',
                'cta_type' => 'donate',
                'sort_order' => 5,
            ],
            [
                'slug' => 'zakat-distribution',
                'title' => ['fr' => 'Distribution de Zakat', 'en' => 'Zakat distribution'],
                'excerpt' => [
                    'fr' => 'Confiez-nous votre Zakat : nous la distribuons intégralement aux bénéficiaires éligibles selon les règles islamiques.',
                    'en' => 'Entrust us with your Zakat: we distribute it in full to eligible beneficiaries according to Islamic rules.',
                ],
                'content' => [
                    'fr' => '<p>La Zakat est le troisième pilier de l\'Islam. Al-Aytaam garantit une distribution conforme aux prescriptions religieuses, directement aux catégories éligibles.</p><h3>Notre engagement</h3><ul><li>100 % de votre Zakat reversée aux bénéficiaires</li><li>Distribution prioritaire aux orphelins, veuves et familles pauvres</li><li>Vérification rigoureuse de l\'éligibilité</li><li>Rapport de distribution transparent</li></ul><p>Purifiez vos biens et transformez des vies en même temps.</p>',
                    'en' => '<p>Zakat is the third pillar of Islam. Al-Aytaam guarantees a distribution that complies with religious prescriptions, directly to eligible categories.</p><h3>Our commitment</h3><ul><li>100% of your Zakat given to beneficiaries</li><li>Priority distribution to orphans, widows and poor families</li><li>Rigorous eligibility verification</li><li>Transparent distribution report</li></ul><p>Purify your wealth and transform lives at the same time.</p>',
                ],
                'icon' => 'Coins',
                'category_slug' => null,
                'cta_type' => 'donate',
                'sort_order' => 6,
            ],
        ];

        foreach ($programs as $data) {
            $categorySlug = $data['category_slug'];
            unset($data['category_slug']);

            Program::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'category_id' => $categorySlug ? $categories[$categorySlug]->id ?? null : null,
                    'is_active' => true,
                ]
            );
        }
    }
}
