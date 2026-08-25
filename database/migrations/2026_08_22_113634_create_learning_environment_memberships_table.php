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
        Schema::create('learning_environment_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_membership_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('learning_environment_id')->index();
            $table->timestamps();

            $table->unique(['school_membership_id', 'learning_environment_id'], 'learning_membership_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_environment_memberships');
    }
};
