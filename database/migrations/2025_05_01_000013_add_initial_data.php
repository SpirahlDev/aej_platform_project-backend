<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertion des Project Types
        DB::table('project_type')->insert([
            ['id' => 1, 'name' => 'Under Development', 'code' => 'DEV', 'created_at' => now()],
            ['id' => 2, 'name' => 'In Creation', 'code' => 'CRE', 'created_at' => now()]
        ]);

        // Insertion des Profiles
        DB::table('profiles')->insert([
            ['name' => 'admin', 'description' => 'Standard administrator with management rights', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'super_admin', 'description' => 'Super administrator with full system access', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'readonly', 'description' => 'Read-only access to the platform', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insertion des Legal Forms
        DB::table('legal_forms')->insert([
            ['name' => 'SA', 'description' => 'Société Anonyme', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SARL', 'description' => 'Société à Responsabilité Limitée', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SARL-U', 'description' => 'Société à Responsabilité Limitée Unipersonnelle', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SNC', 'description' => 'Société en Nom Collectif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SCS', 'description' => 'Société en Commandite Simple', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GIE', 'description' => 'Groupement d\'Intérêt Économique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sole Proprietorship', 'description' => 'Business owned by one individual', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cooperative', 'description' => 'Organization owned and operated by its members', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Association', 'description' => 'Non-profit organization', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insertion de l'Admin User
        DB::table('persons')->insert([
            'id' => 1,
            'last_name' => 'Admin',
            'first_name' => 'System',
            'email' => 'admin@aej-platform.ci',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Récupération de l'ID du profil super_admin
        $super_admin_id = DB::table('profiles')->where('name', 'super_admin')->first()->id;

        // Création du compte admin avec mot de passe hashé
        DB::table('accounts')->insert([
            'person_id' => 1,
            'profile_id' => $super_admin_id,
            'email' => 'admin@aej-platform.ci',
            'password' => Hash::make('admin123'), // Mot de passe hashé avec bcrypt par Laravel
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les données dans l'ordre inverse de celui d'insertion
        DB::table('accounts')->where('email', 'admin@aej-platform.ci')->delete();
        DB::table('persons')->where('id', 1)->delete();
        DB::table('legal_forms')->whereIn('name', ['SA', 'SARL', 'SARL-U', 'SNC', 'SCS', 'GIE', 'Sole Proprietorship', 'Cooperative', 'Association'])->delete();
        DB::table('profiles')->whereIn('name', ['admin', 'super_admin', 'readonly'])->delete();
        DB::table('project_type')->whereIn('id', [1, 2])->delete();
    }
};
