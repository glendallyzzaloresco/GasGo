<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gasgo.test',
            'password' => Hash::make('Admin@123'),
            'phone' => '09123456789',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
