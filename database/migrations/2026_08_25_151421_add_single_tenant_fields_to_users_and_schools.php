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
        Schema::table('schools', function (Blueprint $table): void {
            $table->string('address')->nullable()->after('description');
            $table->string('region')->nullable()->after('address');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->nullable()->after('password')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('school_id');
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });

        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn(['address', 'region']);
        });
    }
};
