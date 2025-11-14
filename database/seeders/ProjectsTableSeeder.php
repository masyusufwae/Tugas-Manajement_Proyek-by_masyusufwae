<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    public function run()
    {
        $projects = [
            [
                'project_name' => 'Website E-commerce',
                'description' => 'Pembangunan website e-commerce dengan fitur lengkap',
                'created_by' => 2, // teamlead1
                'deadline' => '2025-12-31'
            ],
            [
                'project_name' => 'Aplikasi Mobile',
                'description' => 'Pengembangan aplikasi mobile untuk platform iOS dan Android',
                'created_by' => 2, // teamlead1
                'deadline' => '2025-11-30'
            ],
            [
                'project_name' => 'Sistem Inventory',
                'description' => 'Sistem manajemen inventory untuk perusahaan retail',
                'created_by' => 1, // admin
                'deadline' => '2025-10-15'
            ]
        ];

        DB::table('projects')->insert($projects);
    }
}