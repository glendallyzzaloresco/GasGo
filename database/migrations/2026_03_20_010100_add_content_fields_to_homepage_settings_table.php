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
            $table->string('hero_title_prefix')->nullable()->after('promo_banner_image_path');
            $table->string('hero_title_highlight')->nullable()->after('hero_title_prefix');
            $table->string('hero_title_suffix')->nullable()->after('hero_title_highlight');
            $table->text('hero_subtitle')->nullable()->after('hero_title_suffix');
            $table->string('hero_primary_button_label')->nullable()->after('hero_subtitle');
            $table->string('products_section_title')->nullable()->after('hero_primary_button_label');
            $table->string('products_section_subtitle')->nullable()->after('products_section_title');
            $table->string('products_view_all_label')->nullable()->after('products_section_subtitle');
            $table->string('promo_title')->nullable()->after('products_view_all_label');
            $table->text('promo_subtitle')->nullable()->after('promo_title');
            $table->string('promo_button_label')->nullable()->after('promo_subtitle');
            $table->string('contact_address')->nullable()->after('footer_description');
            $table->string('contact_phone')->nullable()->after('contact_address');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('contact_hours')->nullable()->after('contact_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title_prefix',
                'hero_title_highlight',
                'hero_title_suffix',
                'hero_subtitle',
                'hero_primary_button_label',
                'products_section_title',
                'products_section_subtitle',
                'products_view_all_label',
                'promo_title',
                'promo_subtitle',
                'promo_button_label',
                'contact_address',
                'contact_phone',
                'contact_email',
                'contact_hours',
            ]);
        });
    }
};
