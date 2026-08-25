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
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->after('status');
            $table->text('feedback')->nullable()->after('score');
            $table->string('evaluation_status')->default('pending')->after('feedback');
            $table->foreignId('evaluated_by')->nullable()->after('student_id')->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable()->after('submitted_at');
            $table->index(['learning_environment_id', 'evaluation_status'], 'assignment_evaluation_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropIndex('assignment_evaluation_lookup_index');
            $table->dropConstrainedForeignId('evaluated_by');
            $table->dropColumn(['score', 'feedback', 'evaluation_status', 'evaluated_at']);
        });
    }
};
