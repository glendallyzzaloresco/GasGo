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
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name_primary')->default('Gas');
            $table->string('brand_name_accent')->default('Go');
            $table->string('navbar_logo_path')->nullable();
            $table->string('footer_logo_path')->nullable();
            $table->string('home_hero_image_path')->nullable();
            $table->string('promo_banner_image_path')->nullable();
            $table->text('footer_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
