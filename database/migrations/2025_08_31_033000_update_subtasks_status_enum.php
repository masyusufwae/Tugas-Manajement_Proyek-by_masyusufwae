<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update the enum values for the status column in cards table
        DB::statement("ALTER TABLE cards MODIFY COLUMN status ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo'");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("UPDATE cards SET status = 'in_progress' WHERE status = 'review'");
        DB::statement("ALTER TABLE cards MODIFY COLUMN status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo'");
    }
};