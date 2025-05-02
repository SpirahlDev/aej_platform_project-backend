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
            // DemoDataSeeder::class, // Je pourrai l'utiliser pour remplir en amont la bd
        ]);
    }
}
