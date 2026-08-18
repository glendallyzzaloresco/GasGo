<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE riders MODIFY availability ENUM('available', 'busy', 'returning', 'offline') NOT NULL DEFAULT 'offline'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE riders MODIFY availability ENUM('available', 'busy', 'offline') NOT NULL DEFAULT 'offline'");
        }
    }
};
