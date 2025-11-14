<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtasksTableSeeder extends Seeder
{
    public function run()
    {
        $subtasks = [
            // Card 2: Implementasi Authentication
            [
                'card_id' => 2,
                'subtask_title' => 'Buat Login Form',
                'description' => 'Membuat form login dengan validasi',
                'status' => 'done',
                'estimated_hours' => 4.00,
                'actual_hours' => 3.50,
                'position' => 1,
                'reject_reason' => null
            ],
            [
                'card_id' => 2,
                'subtask_title' => 'Buat Register Form',
                'description' => 'Membuat form registrasi dengan validasi',
                'status' => 'in_progress',
                'estimated_hours' => 6.00,
                'actual_hours' => null,
                'position' => 2,
                'reject_reason' => null
            ],
            [
                'card_id' => 2,
                'subtask_title' => 'Implementasi Remember Me',
                'description' => 'Menambahkan fitur remember me',
                'status' => 'todo',
                'estimated_hours' => 3.00,
                'actual_hours' => null,
                'position' => 3,
                'reject_reason' => null
            ],
            
            // Card 4: Design App Wireframe
            [
                'card_id' => 4,
                'subtask_title' => 'Wireframe Home Screen',
                'description' => 'Membuat wireframe untuk home screen',
                'status' => 'todo',
                'estimated_hours' => 4.00,
                'actual_hours' => null,
                'position' => 1,
                'reject_reason' => null
            ],
        ];

        DB::table('subtasks')->insert($subtasks);
    }
}