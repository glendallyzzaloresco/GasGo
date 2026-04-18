<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RehashUserPasswords extends Command
{
    protected $signature = 'users:rehash-passwords {--force}';
    protected $description = 'Rehash all user passwords with argon2id algorithm on next login';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('Password Rehashing System - Argon2id Migration');
        $this->info('═══════════════════════════════════════════════════════════');
        
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->warn('No users found in the database.');
            return;
        }

        $this->info("\nFound " . $users->count() . " user(s) in the system.");
        $this->info("\n📋 How this works:");
        $this->line("1. Existing passwords are already hashed and cannot be directly rehashed");
        $this->line("2. When users log in, Laravel automatically upgrades the hash algorithm");
        $this->line("3. No action is needed - passwords will auto-upgrade on next login");
        
        $this->warn("\n⚠️  ALTERNATIVE: Force immediate rehash with temporary passwords");
        $this->line("Run: php artisan users:rehash-passwords --force");
        $this->line("This will set all users to a temporary password.");
        
        if (!$this->option('force')) {
            $this->info("\n✓ Passwords will automatically upgrade to argon2id on next user login.");
            return;
        }

        // Force rehash with temporary passwords
        if (!$this->confirm("\n⚠️  This will reset ALL user passwords to temporary values!\nUsers will need to use password reset to log in again.\nContinue?")) {
            $this->warn('Operation cancelled.');
            return;
        }

        $this->info("\nGenerating temporary passwords...");
        $count = 0;
        
        foreach ($users as $user) {
            // Generate a temporary password
            $tempPassword = 'Temp' . str_pad($user->id, 4, '0', STR_PAD_LEFT) . '!' . substr(md5($user->email), 0, 8);
            
            // Hash with argon2id (from the new HASH_DRIVER setting)
            $user->update(['password' => Hash::make($tempPassword)]);
            $count++;
            $this->line("✓ Updated user {$user->id} ({$user->email})");
        }

        $this->info("\n✓ Rehashing complete! {$count} passwords updated.");
        $this->warn("⚠️  Users will need to use 'Forgot Password' to reset their password and log in again.");
    }
}
