<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->string('link_token')->unique()->nullable()->after('code');
            $table->foreignId('learning_environment_id')->nullable()->constrained()->nullOnDelete()->after('school_id');
            $table->index(['school_id', 'learning_environment_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'learning_environment_id']);
            $table->dropForeign(['learning_environment_id']);
            $table->dropColumn(['learning_environment_id', 'link_token']);
        });
    }
};
