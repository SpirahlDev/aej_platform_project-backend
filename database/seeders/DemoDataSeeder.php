<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Person;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Promoter;
use App\Models\LegalForm;
use App\Models\Notification;
use App\Models\Document;
use App\Models\AiRecommendation;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Vérifier si le dossier de documents existe
        if (!Storage::exists('public/documents')) {
            Storage::makeDirectory('public/documents');
        }
        
        // Créer des promoteurs de projet (avec leurs personnes associées)
        $this->command->info('Création des promoteurs...');
        $promoters = [];
        for ($i = 0; $i < 20; $i++) {
            $person = Person::create([
                'last_name' => $faker->lastName,
                'first_name' => $faker->firstName,
                'birth_date' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'birth_place' => $faker->city,
                'id_card_number' => $faker->unique()->numerify('CI##########'),
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
            ]);
            
            $promoter = Promoter::create([
                'person_id' => $person->id,
                'additional_info' => $faker->paragraph,
            ]);
            
            $promoters[] = $promoter;
        }
        
        // Récupérer les types de projets et formes juridiques pour les référencer
        $projectTypes = DB::table('project_type')->get();
        $legalForms = LegalForm::all();
        $adminAccounts = Account::whereHas('profile', function($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();
        
        // Si pas d'administrateurs trouvés, utiliser un ID par défaut
        if ($adminAccounts->isEmpty()) {
            $adminAccounts = [
                (object)['id' => 1]
            ];
        }
        
        // Créer des projets avec statuts variés
        $this->command->info('Création des projets...');
        $projects = [];
        $statuses = ['Submitted', 'Under Review', 'Approved', 'Rejected'];
        
        foreach ($promoters as $promoter) {
            // Chaque promoteur a entre 1 et 3 projets
            $numProjects = rand(1, 3);
            
            for ($i = 0; $i < $numProjects; $i++) {
                $status = $faker->randomElement($statuses);
                
                // Préparer les données du projet
                $projectData = [
                    'promoter_id' => $promoter->id,
                    'title' => ucfirst($faker->words(rand(3, 6), true)),
                    'summary' => $faker->sentence,
                    'project_type_id' => $projectTypes->random()->id,
                    'legal_form_id' => $legalForms->random()->id,
                    'description' => $faker->paragraphs(rand(3, 5), true),
                    'status' => $status,
                    'rejection_reason' => $status === 'Rejected' ? $faker->paragraph : null,
                    'submission_date' => $faker->dateTimeBetween('-3 months', '-2 weeks'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Ajouter dates supplémentaires selon le statut
                if (in_array($status, ['Under Review', 'Approved', 'Rejected'])) {
                    $projectData['review_date'] = $faker->dateTimeBetween('-2 weeks', '-1 week');
                    
                    if (in_array($status, ['Approved', 'Rejected'])) {
                        $projectData['decision_date'] = $faker->dateTimeBetween('-1 week', 'now');
                        $projectData['validator_account_id'] = $adminAccounts[0]->id;
                    }
                }
                
                $project = Project::create($projectData);
                $projects[] = $project;
                
                // Créer des documents pour chaque projet
                $this->createDocumentsForProject($project, $faker);
                
                // Créer des notifications pour chaque projet
                $this->createNotificationsForProject($project, $faker);
                
                // Créer des recommandations IA pour certains projets
                if (rand(0, 1) && in_array($status, ['Under Review', 'Approved'])) {
                    $this->createAiRecommendation($project, $faker);
                }
            }
        }
        
        $this->command->info('Toutes les données de démonstration ont été créées avec succès!');
    }
    
    /**
     * Crée les documents pour un projet
     */
    private function createDocumentsForProject(Project $project, $faker): void
    {
        $documentTypes = ['ID_Card', 'Identity_Document', 'Business_Plan'];
        
        // Ajouter document de validation PDF si le projet est approuvé
        if ($project->status === 'Approved') {
            $documentTypes[] = 'Validation_PDF';
        }
        
        // Possibilité d'avoir d'autres documents
        if (rand(0, 1)) {
            $documentTypes[] = 'Other';
        }
        
        foreach ($documentTypes as $type) {
            $fileName = $faker->uuid . '.pdf';
            $filePath = 'documents/' . $fileName;
            $mimeType = 'application/pdf';
            
            if ($type === 'Identity_Document' && rand(0, 1)) {
                $fileName = $faker->uuid . '.jpg';
                $filePath = 'documents/' . $fileName;
                $mimeType = 'image/jpeg';
            }
            
            Document::create([
                'project_id' => $project->id,
                'document_type' => $type,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => rand(100000, 5000000), // Taille entre 100KB et 5MB
                'mime_type' => $mimeType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    /**
     * Crée les notifications pour un projet
     */
    private function createNotificationsForProject(Project $project, $faker): void
    {
        // Notification de réception pour tous les projets
        Notification::create([
            'project_id' => $project->id,
            'notification_type' => 'reception',
            'channel' => 'email',
            'recipient' => $project->promoter->person->email,
            'content' => 'Nous avons bien reçu votre projet "' . $project->title . '". Il sera examiné prochainement.',
            'is_sent' => true,
            'sent_date' => $project->submission_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Notifications supplémentaires selon le statut
        if ($project->status === 'Approved') {
            Notification::create([
                'project_id' => $project->id,
                'notification_type' => 'approval',
                'channel' => 'email',
                'recipient' => $project->promoter->person->email,
                'content' => 'Félicitations! Votre projet "' . $project->title . '" a été approuvé.',
                'is_sent' => true,
                'sent_date' => $project->decision_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif ($project->status === 'Rejected') {
            Notification::create([
                'project_id' => $project->id,
                'notification_type' => 'rejection',
                'channel' => 'email',
                'recipient' => $project->promoter->person->email,
                'content' => 'Votre projet "' . $project->title . '" n\'a malheureusement pas été retenu. Motif: ' . $project->rejection_reason,
                'is_sent' => true,
                'sent_date' => $project->decision_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    /**
     * Crée une recommandation IA pour un projet
     */
    private function createAiRecommendation(Project $project, $faker): void
    {
        $viabilityScore = $faker->randomFloat(2, 1, 5);
        $innovationScore = $faker->randomFloat(2, 1, 5);
        $marketScore = $faker->randomFloat(2, 1, 5);
        
        $recommendations = [];
        
        if ($viabilityScore < 3) {
            $recommendations[] = "Améliorer la viabilité financière en révisant le modèle économique.";
            $recommendations[] = "Considérer des sources de financement alternatives.";
        }
        
        if ($innovationScore < 3) {
            $recommendations[] = "Renforcer l'aspect innovant du projet.";
            $recommendations[] = "Explorer des solutions technologiques plus récentes.";
        }
        
        if ($marketScore < 3) {
            $recommendations[] = "Effectuer une étude de marché plus approfondie.";
            $recommendations[] = "Redéfinir le public cible pour mieux répondre aux besoins du marché.";
        }
        
        // Ajouter quelques recommandations générales
        $recommendations[] = "Envisager des partenariats stratégiques pour accélérer la croissance.";
        $recommendations[] = "Mettre en place un plan de suivi et d'évaluation régulier.";
        
        // Mélanger les recommandations et en prendre quelques-unes
        shuffle($recommendations);
        $finalRecommendations = array_slice($recommendations, 0, rand(3, count($recommendations)));
        
        AiRecommendation::create([
            'project_id' => $project->id,
            'viability_score' => $viabilityScore,
            'innovation_score' => $innovationScore,
            'market_score' => $marketScore,
            'recommendations' => implode("\n\n", $finalRecommendations),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
