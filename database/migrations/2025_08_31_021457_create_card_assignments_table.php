<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('card_assignments', function (Blueprint $table) {
        $table->id('assignment_id');
        $table->unsignedBigInteger('card_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamp('assigned_at')->useCurrent();
        $table->enum('assignment_status', ['assigned','in_progress','completed'])->default('assigned');
        $table->dateTime('started_at')->nullable();
        $table->dateTime('completed_at')->nullable();

        $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
        $table->foreign('user_id')->references('user_id')->on('users');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_assignments');
    }
};
