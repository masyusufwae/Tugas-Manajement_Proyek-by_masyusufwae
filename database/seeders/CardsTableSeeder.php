<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardsTableSeeder extends Seeder
{
    public function run()
    {
        $cards = [
            // Project 1: Website E-commerce
            [
                'board_id' => 1, // To Do board project 1
                'card_title' => 'Design UI/UX Homepage',
                'description' => 'Membuat desain UI/UX untuk halaman homepage',
                'position' => 1,
                'created_by' => 2, // teamlead1
                'due_date' => '2025-09-15',
                'status' => 'todo',
                'priority' => 'high',
                'estimated_hours' => 8.00,
                'actual_hours' => null
            ],
            [
                'board_id' => 2, // In Progress board project 1
                'card_title' => 'Implementasi Authentication',
                'description' => 'Membuat sistem login dan register',
                'position' => 1,
                'created_by' => 2, // teamlead1
                'due_date' => '2025-09-20',
                'status' => 'in_progress',
                'priority' => 'medium',
                'estimated_hours' => 16.00,
                'actual_hours' => null
            ],
            [
                'board_id' => 4, // Done board project 1
                'card_title' => 'Setup Project Environment',
                'description' => 'Menyiapkan environment development',
                'position' => 1,
                'created_by' => 2, // teamlead1
                'due_date' => '2025-09-05',
                'status' => 'done',
                'priority' => 'low',
                'estimated_hours' => 4.00,
                'actual_hours' => 3.50
            ],
            
            // Project 2: Aplikasi Mobile
            [
                'board_id' => 5, // To Do board project 2
                'card_title' => 'Design App Wireframe',
                'description' => 'Membuat wireframe untuk aplikasi mobile',
                'position' => 1,
                'created_by' => 2, // teamlead1
                'due_date' => '2025-10-01',
                'status' => 'todo',
                'priority' => 'medium',
                'estimated_hours' => 12.00,
                'actual_hours' => null
            ],
        ];

        DB::table('cards')->insert($cards);
    }
}