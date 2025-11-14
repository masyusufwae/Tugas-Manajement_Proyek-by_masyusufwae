<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id('help_request_id');
            $table->unsignedBigInteger('subtask_id');
            $table->unsignedBigInteger('requester_id'); // user yang minta bantuan
            $table->unsignedBigInteger('team_lead_id'); // team lead yang diminta bantuan
            $table->text('message');
            $table->enum('status', ['pending', 'responded', 'resolved', 'rejected'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys
            $table->foreign('subtask_id')->references('subtask_id')->on('subtasks')->onDelete('cascade');
            $table->foreign('requester_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('team_lead_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_requests');
    }
};
