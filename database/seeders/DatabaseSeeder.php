<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            ProjectsTableSeeder::class,
            ProjectMembersTableSeeder::class,
            BoardsTableSeeder::class,
            CardsTableSeeder::class,
            CardAssignmentsTableSeeder::class,
            SubtasksTableSeeder::class,
            TimeLogsTableSeeder::class,
        ]);
    }
}
