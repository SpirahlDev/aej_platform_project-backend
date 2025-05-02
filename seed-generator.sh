#!/bin/bash
# Script de création des seeders Laravel pour la plateforme de gestion des projets
# À exécuter depuis la racine du projet Laravel

# Couleurs pour une meilleure lisibilité
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérification du dossier Seeders
SEEDERS_DIR="database/seeders"

if [ ! -d "$SEEDERS_DIR" ]; then
    mkdir -p "$SEEDERS_DIR"
    echo -e "${GREEN}Dossier $SEEDERS_DIR créé${NC}"
else
    echo -e "${YELLOW}Dossier $SEEDERS_DIR existe déjà${NC}"
fi

# Vérification du dossier Models si les modèles manquants doivent être créés
MODELS_DIR="app/Models"
if [ ! -d "$MODELS_DIR" ]; then
    mkdir -p "$MODELS_DIR"
    echo -e "${GREEN}Dossier $MODELS_DIR créé${NC}"
fi

# Fonction pour créer un seeder
create_seeder() {
    local name=$1
    local content=$2
    local filepath="$SEEDERS_DIR/$name.php"
    
    echo -e "${GREEN}Création du seeder $name...${NC}"
    echo "$content" > "$filepath"
    
    echo -e "${GREEN}Seeder $name.php créé avec succès${NC}"
}

# Fonction pour créer un modèle
create_model() {
    local name=$1
    local content=$2
    local filepath="$MODELS_DIR/$name.php"
    
    echo -e "${GREEN}Création du modèle $name...${NC}"
    echo "$content" > "$filepath"
    
    echo -e "${GREEN}Modèle $name.php créé avec succès${NC}"
}

# Vérifier/Créer les modèles manquants
# Modèle LegalForm
legal_form_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalForm extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description'
    ];
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
EOF
)

# Modèle Promoter
promoter_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'person_id',
        'additional_info'
    ];
    
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
EOF
)

# Modèle Project 
project_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'promoter_id',
        'title',
        'summary',
        'legal_form_id',
        'description',
        'status',
        'rejection_reason',
        'submission_date',
        'review_date',
        'decision_date',
        'validator_account_id',
        'project_type_id'
    ];
    
    protected $casts = [
        'submission_date' => 'datetime',
        'review_date' => 'datetime',
        'decision_date' => 'datetime',
    ];
    
    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }
    
    public function legalForm()
    {
        return $this->belongsTo(LegalForm::class);
    }
    
    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }
    
    public function validator()
    {
        return $this->belongsTo(Account::class, 'validator_account_id');
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    public function aiRecommendation()
    {
        return $this->hasOne(AiRecommendation::class);
    }
}
EOF
)

# Modèle Document
document_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'project_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
EOF
)

# Modèle Notification
notification_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'project_id',
        'notification_type',
        'channel',
        'recipient',
        'content',
        'is_sent',
        'sent_date'
    ];
    
    protected $casts = [
        'is_sent' => 'boolean',
        'sent_date' => 'datetime'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
EOF
)

# Modèle AiRecommendation
ai_recommendation_model=$(cat << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'project_id',
        'viability_score',
        'innovation_score',
        'market_score',
        'recommendations'
    ];
    
    protected $casts = [
        'viability_score' => 'float',
        'innovation_score' => 'float',
        'market_score' => 'float'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
EOF
)

# DatabaseSeeder principal
database_seeder=$(cat << 'EOF'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProfileSeeder::class,
            LegalFormSeeder::class,
            ProjectTypeSeeder::class,
            AdminUserSeeder::class,
            // DemoDataSeeder::class, // Décommentez uniquement pour les environnements de développement/test
        ]);
    }
}
EOF
)

# ProfileSeeder
profile_seeder=$(cat << 'EOF'
<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'super_admin',
                'description' => 'Super administrateur avec accès complet au système',
                'permissions' => json_encode([
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.validate',
                    'reports.view', 'reports.create',
                    'settings.view', 'settings.edit'
                ])
            ],
            [
                'name' => 'admin',
                'description' => 'Administrateur standard avec droits de gestion',
                'permissions' => json_encode([
                    'users.view',
                    'projects.view', 'projects.edit', 'projects.validate',
                    'reports.view', 'reports.create'
                ])
            ],
            [
                'name' => 'moderator',
                'description' => 'Modérateur qui peut examiner les projets',
                'permissions' => json_encode([
                    'projects.view', 'projects.edit',
                    'reports.view'
                ])
            ],
            [
                'name' => 'readonly',
                'description' => 'Accès en lecture seule à la plateforme',
                'permissions' => json_encode([
                    'projects.view',
                    'reports.view'
                ])
            ]
        ];

        foreach ($profiles as $profile) {
            Profile::updateOrCreate(
                ['name' => $profile['name']],
                $profile
            );
        }
    }
}
EOF
)

# LegalFormSeeder
legal_form_seeder=$(cat << 'EOF'
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
EOF
)

# ProjectTypeSeeder
project_type_seeder=$(cat << 'EOF'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectTypes = [
            [
                'name' => 'Under Development',
                'code' => 'DEV',
                'created_at' => now()
            ],
            [
                'name' => 'In Creation',
                'code' => 'CRE',
                'created_at' => now()
            ],
            [
                'name' => 'Expansion',
                'code' => 'EXP',
                'created_at' => now()
            ],
            [
                'name' => 'Restructuring',
                'code' => 'RES',
                'created_at' => now()
            ],
            [
                'name' => 'Social Enterprise',
                'code' => 'SOC',
                'created_at' => now()
            ],
            [
                'name' => 'Digital Innovation',
                'code' => 'DIG',
                'created_at' => now()
            ],
            [
                'name' => 'Agricultural Project',
                'code' => 'AGR',
                'created_at' => now()
            ]
        ];

        // Nous utilisons directement DB car ProjectType a timestamps = false et softDeletes
        foreach ($projectTypes as $type) {
            DB::table('project_type')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
EOF
)

# AdminUserSeeder
admin_user_seeder=$(cat << 'EOF'
<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Account;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'administrateur principal
        $this->createAdmin(
            'Admin',
            'System',
            'admin@aej-platform.ci',
            'admin123',
            'super_admin'
        );
        
        // Créer un administrateur secondaire
        $this->createAdmin(
            'Kouassi',
            'Jean',
            'jean.kouassi@aej-platform.ci',
            'password123',
            'admin'
        );
        
        // Créer un modérateur
        $this->createAdmin(
            'Diallo',
            'Fatou',
            'fatou.diallo@aej-platform.ci',
            'password123',
            'moderator'
        );
        
        // Créer un utilisateur en lecture seule
        $this->createAdmin(
            'Koffi',
            'Pierre',
            'pierre.koffi@aej-platform.ci',
            'password123',
            'readonly'
        );
    }
    
    /**
     * Crée un utilisateur administratif
     */
    private function createAdmin(string $lastName, string $firstName, string $email, string $password, string $profileName): void
    {
        // Trouver le profil
        $profile = Profile::where('name', $profileName)->first();
        
        if (!$profile) {
            $this->command->error("Profil '$profileName' non trouvé. Veuillez exécuter ProfileSeeder d'abord.");
            return;
        }
        
        // Créer ou mettre à jour la personne
        $person = Person::updateOrCreate(
            ['email' => $email],
            [
                'last_name' => $lastName,
                'first_name' => $firstName,
                'email' => $email,
            ]
        );
        
        // Créer ou mettre à jour le compte
        Account::updateOrCreate(
            ['email' => $email],
            [
                'person_id' => $person->id,
                'profile_id' => $profile->id,
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );
        
        $this->command->info("Utilisateur $firstName $lastName ($profileName) créé avec succès.");
    }
}
EOF
)

# DemoDataSeeder
demo_data_seeder=$(cat << 'EOF'
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
EOF
)

# Créer les modèles manquants si nécessaires
if [ ! -f "$MODELS_DIR/LegalForm.php" ]; then
    create_model "LegalForm" "$legal_form_model"
fi

if [ ! -f "$MODELS_DIR/Promoter.php" ]; then
    create_model "Promoter" "$promoter_model"
fi

if [ ! -f "$MODELS_DIR/Project.php" ]; then
    create_model "Project" "$project_model"
fi

if [ ! -f "$MODELS_DIR/Document.php" ]; then
    create_model "Document" "$document_model"
fi

if [ ! -f "$MODELS_DIR/Notification.php" ]; then
    create_model "Notification" "$notification_model"
fi

if [ ! -f "$MODELS_DIR/AiRecommendation.php" ]; then
    create_model "AiRecommendation" "$ai_recommendation_model"
fi

# Créer les seeders
create_seeder "DatabaseSeeder" "$database_seeder"
create_seeder "ProfileSeeder" "$profile_seeder"
create_seeder "LegalFormSeeder" "$legal_form_seeder"
create_seeder "ProjectTypeSeeder" "$project_type_seeder"
create_seeder "AdminUserSeeder" "$admin_user_seeder"
create_seeder "DemoDataSeeder" "$demo_data_seeder"

echo -e "${YELLOW}================================================================================${NC}"
echo -e "${GREEN}Tous les seeders Laravel ont été créés avec succès dans le dossier database/seeders/${NC}"
echo -e "${GREEN}Les modèles requis ont également été créés ou vérifiés dans app/Models/${NC}"
echo -e "${YELLOW}Pour exécuter les seeders, utilisez la commande : ${NC}php artisan db:seed"
echo -e "${YELLOW}================================================================================${NC}"
echo -e "${GREEN}L'ordre d'exécution dans DatabaseSeeder respecte les dépendances des données.${NC}"
echo -e "${GREEN}Le seeder DemoDataSeeder est inclus mais commenté par défaut - activez-le uniquement${NC}"
echo -e "${GREEN}pour les environnements de développement ou de test.${NC}"
echo -e "${YELLOW}================================================================================${NC}"
echo -e "${GREEN}Pour exécuter un seeder spécifique, utilisez :${NC}"
echo -e "php artisan db:seed --class=AdminUserSeeder"
echo -e "php artisan db:seed --class=DemoDataSeeder"
echo -e "${YELLOW}================================================================================${NC}"

# Rendre le script exécutable

echo -e "${GREEN}Script terminé avec succès!${NC}"isan db:seed --class=ProfileSeeder"
echo -e "php artisan db:seed --class=LegalFormSeeder"
echo -e "php artisan db:seed --class=ProjectTypeSeeder"
echo -e "php art