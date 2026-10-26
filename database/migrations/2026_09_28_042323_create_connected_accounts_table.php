<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connected_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['linkedin', 'naukri', 'indeed']);
            $table->text('access_token')->nullable(); // For OAuth (LinkedIn)
            $table->text('refresh_token')->nullable();
            $table->string('account_identifier')->nullable(); // Email or Username for scraper login
            $table->text('password')->nullable(); // Encrypted password for scraper login (Naukri/Indeed)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connected_accounts');
    }
};
