<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tailored_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->text('job_description')->nullable();
            $table->longText('tailored_content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailored_resumes');
    }
};
