<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardAssignmentsTableSeeder extends Seeder
{
    public function run()
    {
        $assignments = [
            // Card 1: Design UI/UX Homepage
            [
                'card_id' => 1,
                'user_id' => 5, // designer1
                'assignment_status' => 'assigned',
                'started_at' => null,
                'completed_at' => null
            ],
            
            // Card 2: Implementasi Authentication
            [
                'card_id' => 2,
                'user_id' => 3, // dev1
                'assignment_status' => 'in_progress',
                'started_at' => now()->subDays(2),
                'completed_at' => null
            ],
            
            // Card 3: Setup Project Environment
            [
                'card_id' => 3,
                'user_id' => 3, // dev1
                'assignment_status' => 'completed',
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(3)
            ],
            
            // Card 4: Design App Wireframe
            [
                'card_id' => 4,
                'user_id' => 4, // dev2
                'assignment_status' => 'assigned',
                'started_at' => null,
                'completed_at' => null
            ],
        ];

        DB::table('card_assignments')->insert($assignments);
    }
}