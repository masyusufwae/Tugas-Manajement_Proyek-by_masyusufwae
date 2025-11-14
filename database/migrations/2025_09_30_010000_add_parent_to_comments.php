<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_comment_id')->nullable()->after('user_id');
            $table->foreign('parent_comment_id')
                ->references('comment_id')
                ->on('card_comments')
                ->onDelete('cascade');
        });

        Schema::table('subtask_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_comment_id')->nullable()->after('user_id');
            $table->foreign('parent_comment_id')
                ->references('comment_id')
                ->on('subtask_comments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('card_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_comment_id']);
            $table->dropColumn('parent_comment_id');
        });

        Schema::table('subtask_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_comment_id']);
            $table->dropColumn('parent_comment_id');
        });
    }
};
