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
        Schema::table('learning_environment_memberships', function (Blueprint $table) {
            $table->foreign('learning_environment_id')->references('id')->on('learning_environments')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_environment_memberships', function (Blueprint $table) {
            $table->dropForeign(['learning_environment_id']);
        });
    }
};
