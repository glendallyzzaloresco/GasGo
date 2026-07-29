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
        Schema::table('homepage_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('homepage_settings', 'primary_color')) {
                $table->string('primary_color', 7)->nullable()->default('#1a6db0');
            }
            if (! Schema::hasColumn('homepage_settings', 'accent_color')) {
                $table->string('accent_color', 7)->nullable()->default('#f7941d');
            }
            if (! Schema::hasColumn('homepage_settings', 'background_color')) {
                $table->string('background_color', 7)->nullable()->default('#f4f7fb');
            }
            if (! Schema::hasColumn('homepage_settings', 'sidebar_bg_color')) {
                $table->string('sidebar_bg_color', 7)->nullable()->default('#111b35');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            if (Schema::hasColumn('homepage_settings', 'primary_color')) {
                $table->dropColumn('primary_color');
            }
            if (Schema::hasColumn('homepage_settings', 'accent_color')) {
                $table->dropColumn('accent_color');
            }
            if (Schema::hasColumn('homepage_settings', 'background_color')) {
                $table->dropColumn('background_color');
            }
            if (Schema::hasColumn('homepage_settings', 'sidebar_bg_color')) {
                $table->dropColumn('sidebar_bg_color');
            }
        });
    }
};
