<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE subtasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo'");
    }

    public function down(): void
    {
        DB::statement("UPDATE subtasks SET status = 'done' WHERE status = 'review'");
        DB::statement("ALTER TABLE subtasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo'");
    }
};
