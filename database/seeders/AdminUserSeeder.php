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
            'Alloue',
            'Yapi',
            'alloue.yapi@yopmail.com',
            'admin123',
            'super_admin'
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
                'password' => bcrypt($password),
                'is_active' => true,
            ]
        );

        $this->command->info("Utilisateur $firstName $lastName ($profileName) créé avec succès.");
    }
}
