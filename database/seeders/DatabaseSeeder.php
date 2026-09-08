<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only 1 Admin Account
        User::firstOrCreate(
            ['email' => 'admin@gasgo.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'phone' => '09123456789',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
