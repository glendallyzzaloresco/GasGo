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
            if (! Schema::hasColumn('homepage_settings', 'industry_noun')) {
                $table->string('industry_noun')->nullable()->default('LPG');
            }
            if (! Schema::hasColumn('homepage_settings', 'how_it_works_title')) {
                $table->string('how_it_works_title')->nullable()->default('How It Works');
            }
            if (! Schema::hasColumn('homepage_settings', 'how_it_works_subtitle')) {
                $table->string('how_it_works_subtitle')->nullable()->default('Order in 4 easy steps');
            }
            if (! Schema::hasColumn('homepage_settings', 'why_choose_title')) {
                $table->string('why_choose_title')->nullable()->default('Why Choose Us');
            }
            if (! Schema::hasColumn('homepage_settings', 'why_choose_subtitle')) {
                $table->string('why_choose_subtitle')->nullable()->default('Fast, safe, and reliable service');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $columns = [
                'industry_noun',
                'how_it_works_title',
                'how_it_works_subtitle',
                'why_choose_title',
                'why_choose_subtitle',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('homepage_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
