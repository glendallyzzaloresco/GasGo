<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name_primary',
        'brand_name_accent',
        'navbar_logo_path',
        'footer_logo_path',
        'home_hero_image_path',
        'promo_banner_image_path',
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
        'footer_description',
        'contact_address',
        'contact_phone',
        'contact_email',
        'contact_hours',
        'gcash_account_number',
        'gcash_account_name',
    ];

    public static function singleton(): self
    {
        $settings = static::query()->first();

        if ($settings) {
            return $settings;
        }

        return static::create([
            'brand_name_primary' => 'Gas',
            'brand_name_accent' => 'Go',
            'hero_title_prefix' => 'Fast, Reliable',
            'hero_title_highlight' => 'LPG Delivery',
            'hero_title_suffix' => 'to Your Door',
            'hero_subtitle' => 'Fast, reliable LPG delivery right to your door. Earn loyalty rewards with every order.',
            'hero_primary_button_label' => 'Browse Products',
            'products_section_title' => 'Our Products',
            'products_section_subtitle' => 'Choose from our range of LPG tanks and accessories',
            'products_view_all_label' => 'View All Products',
            'promo_title' => 'New User? Get FREE Delivery on Your First Order!',
            'promo_subtitle' => 'Register now and start earning loyalty points with every purchase.',
            'promo_button_label' => 'Register Now',
            'footer_description' => 'Your trusted partner for fast, reliable LPG delivery. Track your orders in real-time and earn rewards with every purchase.',
            'contact_address' => 'PNR Site Estacion San Miguel Calasiao Pangasinan',
            'contact_phone' => '+63 912 345 6789',
            'contact_email' => 'info@gasgo.com',
            'contact_hours' => 'Mon-Sun: 6AM - 10PM',
        ]);
    }

    public function getNavbarLogoUrlAttribute(): string
    {
        return $this->resolveAssetUrl($this->navbar_logo_path, 'images/logo-gasgo.png');
    }

    public function getFooterLogoUrlAttribute(): string
    {
        return $this->resolveAssetUrl($this->footer_logo_path, 'images/logo-gasgo.png');
    }

    public function getHomeHeroImageUrlAttribute(): ?string
    {
        return $this->resolveAssetUrl($this->home_hero_image_path);
    }

    public function getPromoBannerImageUrlAttribute(): ?string
    {
        return $this->resolveAssetUrl($this->promo_banner_image_path);
    }

    private function resolveAssetUrl(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback ? asset($fallback) : null;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            return $path;
        }

        if (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'images/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }
}
