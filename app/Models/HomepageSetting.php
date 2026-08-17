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
        'gcash_image_path',
        'payment_methods',
        'delivery_fee',
        'primary_color',
        'accent_color',
        'background_color',
        'sidebar_bg_color',
        'industry_noun',
        'how_it_works_title',
        'how_it_works_subtitle',
        'why_choose_title',
        'why_choose_subtitle',
    ];

    protected $casts = [
        'payment_methods' => 'array',
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
            'hero_title_highlight' => 'Delivery Service',
            'hero_title_suffix' => 'to Your Door',
            'hero_subtitle' => 'Fast, reliable delivery right to your door. Track your orders in real-time and earn rewards.',
            'hero_primary_button_label' => 'Browse Products',
            'products_section_title' => 'Our Products',
            'products_section_subtitle' => 'Choose from our wide range of quality products',
            'products_view_all_label' => 'View All Products',
            'promo_title' => 'Get FREE items with every order!',
            'promo_subtitle' => 'Register now and start earning loyalty points with every purchase.',
            'promo_button_label' => 'Register Now',
            'footer_description' => 'Your trusted partner for fast, reliable delivery. Track your orders in real-time and earn rewards with every purchase.',
            'contact_address' => 'PNR Site Estacion San Miguel Calasiao Pangasinan',
            'contact_phone' => '+63 912 345 6789',
            'contact_email' => 'info@gasgo.com',
            'contact_hours' => 'Mon-Sun: 6AM - 10PM',
            'delivery_fee' => 50.00,
            'primary_color' => '#1a6db0',
            'accent_color' => '#f7941d',
            'background_color' => '#f4f7fb',
            'sidebar_bg_color' => '#111b35',
            'industry_noun' => 'Products',
            'how_it_works_title' => 'How It Works',
            'how_it_works_subtitle' => 'Order in 4 easy steps',
            'why_choose_title' => 'Why Choose Us',
            'why_choose_subtitle' => 'We make delivery convenient, safe, and rewarding',
        ]);
    }

    /**
     * Return built-in and custom payment methods for checkout.
     */
    public function availablePaymentMethods(): array
    {
        $methods = [
            [
                'key' => 'cash',
                'label' => 'Cash on Delivery',
                'description' => 'Pay when you receive your order',
                'icon' => 'fas fa-money-bill-wave',
                'color' => 'cash',
                'requires_proof' => false,
                'instructions' => 'No upload required.',
            ],
        ];

        $gcashName = filled($this->gcash_account_name) ? $this->gcash_account_name : 'GasGo LPG Hub';
        $gcashNumber = filled($this->gcash_account_number) ? $this->gcash_account_number : '0917 123 4567';

        $methods[] = [
            'key' => 'gcash',
            'label' => 'GCash',
            'description' => 'Pay via GCash e-wallet',
            'icon' => 'fas fa-mobile-alt',
            'color' => 'gcash',
            'requires_proof' => true,
            'account_name' => $gcashName,
            'account_number' => $gcashNumber,
            'image_url' => $this->resolveAssetUrl($this->gcash_image_path),
            'instructions' => 'After payment, upload a screenshot or photo of your proof of payment.',
        ];

        foreach ((array) ($this->payment_methods ?? []) as $method) {
            if (!is_array($method)) {
                continue;
            }

            $key = strtolower(trim((string) ($method['key'] ?? '')));
            if ($key === '' || in_array($key, ['cash', 'gcash'], true)) {
                continue;
            }

            $methods[] = [
                'key' => $key,
                'label' => trim((string) ($method['label'] ?? ucfirst(str_replace('_', ' ', $key)))),
                'account_name' => trim((string) ($method['account_name'] ?? '')),
                'account_number' => trim((string) ($method['account_number'] ?? '')),
                'requires_proof' => true,
                'image_url' => $this->resolveAssetUrl($method['image_path'] ?? null),
                'description' => trim((string) ($method['account_name'] ?? '') . ' ' . (string) ($method['account_number'] ?? '')),
                'icon' => 'fas fa-credit-card',
                'color' => 'info',
                'instructions' => 'After payment, upload proof of payment before placing your order.',
            ];
        }

        return $methods;
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

        if (str_starts_with($normalized, 'images/')) {
            return asset($normalized);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        if (file_exists(public_path('storage/' . $normalized))) {
            return asset('storage/' . $normalized);
        }

        if (file_exists(storage_path('app/public/' . $normalized))) {
            return asset('storage/' . $normalized);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($normalized)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
        }

        return \Illuminate\Support\Facades\Storage::url($normalized);
    }
}
