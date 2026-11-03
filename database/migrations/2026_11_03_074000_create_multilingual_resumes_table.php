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
        Schema::create('multilingual_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language_name');       // e.g. "German", "Spanish", "Japanese"
            $table->string('language_code', 20);   // e.g. "de", "es", "ja", "custom"
            $table->string('language_flag', 20)->default('🌐'); // emoji flag
            $table->unsignedInteger('template_id')->default(1);
            $table->string('target_role')->nullable();
            $table->longText('resume_data');       // JSON formatted resume matching ATS schema
            $table->longText('raw_content')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multilingual_resumes');
    }
};

