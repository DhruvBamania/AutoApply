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
        Schema::create('mock_interview_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interview_preparation_id')->nullable()->constrained('interview_preparations')->nullOnDelete();
            $table->string('role_title');
            $table->string('experience_level');
            $table->text('tech_stack');
            $table->string('status')->default('in_progress'); // in_progress, completed, abandoned
            $table->unsignedInteger('current_question_index')->default(1);
            $table->unsignedInteger('total_questions')->default(5);
            $table->longText('conversation_history'); // JSON array of messages
            $table->longText('evaluation_report')->nullable(); // JSON object of final scorecard
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mock_interview_sessions');
    }
};
