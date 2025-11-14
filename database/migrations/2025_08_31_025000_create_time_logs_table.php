<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('card_id')->nullable();
            $table->unsignedBigInteger('subtask_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('description')->nullable();

            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('subtask_id')->references('subtask_id')->on('subtasks')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
