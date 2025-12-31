<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed AI settings
        $this->call(AISettingsSeeder::class);

        // For local development, you can uncomment this to create test users:
        // User::factory(10)->create();

        // To create a super admin, use: php artisan make:superadmin
    }
}
