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
        ];

        foreach ($profiles as $profile) {
            Profile::updateOrCreate(
                ['name' => $profile['name']],
                $profile
            );
        }
    }
}
