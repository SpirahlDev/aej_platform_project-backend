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
            // [
            //     'name' => 'Expansion',
            //     'code' => 'EXP',
            //     'created_at' => now()
            // ],
            // [
            //     'name' => 'Restructuring',
            //     'code' => 'RES',
            //     'created_at' => now()
            // ],
            // [
            //     'name' => 'Social Enterprise',
            //     'code' => 'SOC',
            //     'created_at' => now()
            // ],
            // [
            //     'name' => 'Digital Innovation',
            //     'code' => 'DIG',
            //     'created_at' => now()
            // ],
            // [
            //     'name' => 'Agricultural Project',
            //     'code' => 'AGR',
            //     'created_at' => now()
            // ]
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
