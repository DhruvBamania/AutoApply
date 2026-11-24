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
        Schema::create('saved_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->string('location')->nullable();
            $table->string('salary')->nullable();
            $table->string('job_type')->nullable();
            $table->text('job_url')->nullable();
            $table->string('platform')->default('Other');
            $table->longText('job_description')->nullable();
            $table->integer('match_score')->nullable();
            $table->json('match_analysis')->nullable();
            $table->string('status')->default('bookmarked');
            $table->timestamp('applied_at')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('saved_applications');
    }
};

