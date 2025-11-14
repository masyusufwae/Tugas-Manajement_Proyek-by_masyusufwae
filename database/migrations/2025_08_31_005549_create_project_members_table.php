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
    Schema::create('project_members', function (Blueprint $table) {
        $table->id('member_id');
        $table->unsignedBigInteger('project_id');
        $table->unsignedBigInteger('user_id');
        $table->enum('role', ['admin','member'])->default('member');
        $table->timestamp('joined_at')->useCurrent();

        $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
        $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
