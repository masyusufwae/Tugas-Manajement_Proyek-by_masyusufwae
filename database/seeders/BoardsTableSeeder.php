<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardsTableSeeder extends Seeder
{
    public function run()
    {
        $boards = [
            // Project 1: Website E-commerce
            ['project_id' => 1, 'board_name' => 'To Do', 'position' => 1],
            ['project_id' => 1, 'board_name' => 'In Progress', 'position' => 2],
            ['project_id' => 1, 'board_name' => 'Review', 'position' => 3],
            ['project_id' => 1, 'board_name' => 'Done', 'position' => 4],

            // Project 2: Aplikasi Mobile
            ['project_id' => 2, 'board_name' => 'To Do', 'position' => 1],
            ['project_id' => 2, 'board_name' => 'In Progress', 'position' => 2],
            ['project_id' => 2, 'board_name' => 'Review', 'position' => 3],
            ['project_id' => 2, 'board_name' => 'Done', 'position' => 4],

            // Project 3: Sistem Inventory
            ['project_id' => 3, 'board_name' => 'To Do', 'position' => 1],
            ['project_id' => 3, 'board_name' => 'In Progress', 'position' => 2],
            ['project_id' => 3, 'board_name' => 'Review', 'position' => 3],
            ['project_id' => 3, 'board_name' => 'Done', 'position' => 4],
        ];

        DB::table('boards')->insert($boards);
    }
}