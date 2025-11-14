<?php

// database/migrations/2025_xx_xx_create_subtasks_table.php
// database/migrations/2025_08_31_024000_create_subtasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id('subtask_id'); // BIGINT UNSIGNED AUTO_INCREMENT
            $table->unsignedBigInteger('card_id'); // harus sama dengan cards.card_id
            $table->string('subtask_title', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['todo','in_progress','review','done'])->default('todo');
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->integer('position')->default(1);
             $table->text('reject_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign key
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
