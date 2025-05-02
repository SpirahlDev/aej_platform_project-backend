<?php

namespace Database\Seeders;

use App\Models\LegalForm;
use Illuminate\Database\Seeder;

class LegalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legalForms = [
            [
                'name' => 'SA',
                'description' => 'Société Anonyme - Entreprise dont le capital est divisé en actions et qui peut faire appel à l\'épargne publique.'
            ],
            [
                'name' => 'SARL',
                'description' => 'Société à Responsabilité Limitée - Entreprise commerciale où la responsabilité est limitée aux apports.'
            ],
            [
                'name' => 'SARL-U',
                'description' => 'Société à Responsabilité Limitée Unipersonnelle - SARL constituée d\'un seul associé.'
            ],
            [
                'name' => 'SNC',
                'description' => 'Société en Nom Collectif - Société où tous les associés ont la qualité de commerçant et répondent indéfiniment et solidairement des dettes sociales.'
            ],
            [
                'name' => 'SCS',
                'description' => 'Société en Commandite Simple - Société dans laquelle coexistent un ou plusieurs associés indéfiniment responsables et un ou plusieurs associés responsables uniquement à concurrence de leurs apports.'
            ],
            [
                'name' => 'GIE',
                'description' => 'Groupement d\'Intérêt Économique - Structure qui permet à ses membres de mettre en commun certaines activités afin de développer leurs entreprises.'
            ],
            [
                'name' => 'Sole Proprietorship',
                'description' => 'Entreprise Individuelle - Entreprise détenue et dirigée par une seule personne physique.'
            ],
            [
                'name' => 'Cooperative',
                'description' => 'Coopérative - Organisation détenue et gérée par ses membres, qui fonctionnent selon le principe démocratique (1 personne = 1 voix).'
            ],
            [
                'name' => 'Association',
                'description' => 'Organisation à but non lucratif - Groupement de personnes volontaires réunies autour d\'un projet commun ou partageant des activités, sans chercher à réaliser de bénéfices.'
            ],
            [
                'name' => 'SAS',
                'description' => 'Société par Actions Simplifiée - Société commerciale offrant une grande liberté contractuelle aux associés.'
            ],
            [
                'name' => 'SCI',
                'description' => 'Société Civile Immobilière - Société civile ayant pour objet la propriété, la gestion et l\'administration d\'un ou plusieurs immeubles.'
            ]
        ];

        foreach ($legalForms as $legalForm) {
            LegalForm::updateOrCreate(
                ['name' => $legalForm['name']],
                $legalForm
            );
        }
    }
}
