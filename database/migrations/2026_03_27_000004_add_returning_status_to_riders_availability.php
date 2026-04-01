<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the availability enum to include 'returning' status
        DB::statement("ALTER TABLE riders MODIFY availability ENUM('available', 'busy', 'returning', 'offline') NOT NULL DEFAULT 'offline'");
    }

    public function down(): void
    {
        // Revert to the original enum without 'returning'
        DB::statement("ALTER TABLE riders MODIFY availability ENUM('available', 'busy', 'offline') NOT NULL DEFAULT 'offline'");
    }
};
