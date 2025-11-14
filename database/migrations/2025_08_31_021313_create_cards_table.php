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
    Schema::create('cards', function (Blueprint $table) {
        $table->id('card_id');
        $table->unsignedBigInteger('board_id');
        $table->string('card_title', 100);
        $table->text('description')->nullable();
        $table->integer('position')->default(0);
        $table->unsignedBigInteger('created_by');
        $table->timestamp('created_at')->useCurrent();
        $table->date('due_date')->nullable();
        $table->enum('status', ['todo','in_progress','done'])->default('todo');
        $table->enum('priority', ['low','medium','high'])->default('medium');
        $table->decimal('estimated_hours', 5, 2)->nullable();
        $table->decimal('actual_hours', 5, 2)->nullable();

        $table->foreign('board_id')->references('board_id')->on('boards')->onDelete('cascade');
        $table->foreign('created_by')->references('user_id')->on('users');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
