<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeLogsTableSeeder extends Seeder
{
    public function run()
    {
        $timeLogs = [
            // Log untuk Card 2, Subtask 1 (Buat Login Form)
            [
                'card_id' => 2,
                'subtask_id' => 1,
                'user_id' => 3, // dev1
                'start_time' => now()->subDays(2)->setTime(9, 0),
                'end_time' => now()->subDays(2)->setTime(12, 30),
                'duration_minutes' => 210, // 3.5 jam
                'description' => 'Membuat form login dengan validasi email dan password'
            ],
            [
                'card_id' => 2,
                'subtask_id' => 1,
                'user_id' => 3, // dev1
                'start_time' => now()->subDays(2)->setTime(13, 30),
                'end_time' => now()->subDays(2)->setTime(15, 0),
                'duration_minutes' => 90, // 1.5 jam
                'description' => 'Menambahkan styling pada form login'
            ],
            
            // Log untuk Card 2, Subtask 2 (Buat Register Form)
            [
                'card_id' => 2,
                'subtask_id' => 2,
                'user_id' => 3, // dev1
                'start_time' => now()->subDays(1)->setTime(10, 0),
                'end_time' => now()->subDays(1)->setTime(12, 0),
                'duration_minutes' => 120, // 2 jam
                'description' => 'Membuat struktur form registrasi'
            ],
            
            // Log untuk Card 3 (Setup Project Environment)
            [
                'card_id' => 3,
                'subtask_id' => null,
                'user_id' => 3, // dev1
                'start_time' => now()->subDays(5)->setTime(14, 0),
                'end_time' => now()->subDays(5)->setTime(17, 30),
                'duration_minutes' => 210, // 3.5 jam
                'description' => 'Setup environment development dan install dependencies'
            ],
        ];

        DB::table('time_logs')->insert($timeLogs);
    }
}