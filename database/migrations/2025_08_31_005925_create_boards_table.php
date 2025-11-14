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
    Schema::create('boards', function (Blueprint $table) {
        $table->id('board_id');
        $table->unsignedBigInteger('project_id');
        $table->string('board_name', 100);
        $table->text('description')->nullable();
        $table->integer('position')->default(0);
        $table->timestamp('created_at')->useCurrent();

        $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
