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
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->string('connection_type')->default('credentials')->after('platform'); // oauth, credentials, api_key
            $table->json('extra_data')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->dropColumn(['connection_type', 'extra_data']);
        });
    }
};
