<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Kept empty by default in production so no dummy or test data is inserted into live databases.
     * To seed demo data for local mobile testing, run: php artisan db:seed --class=MobileDemoSeeder
     */
    public function run(): void
    {
        // No default dummy users.
    }
}
