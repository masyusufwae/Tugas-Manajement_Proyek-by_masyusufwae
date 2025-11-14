<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id('project_id');
        $table->string('project_name', 100);
        $table->text('description')->nullable();
        $table->unsignedBigInteger('created_by');
        $table->timestamp('created_at')->useCurrent();
        $table->date('deadline')->nullable();

        $table->foreign('created_by')->references('user_id')->on('users');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
