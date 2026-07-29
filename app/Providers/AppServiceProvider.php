<?php

namespace App\Providers;

use App\Models\HomepageSetting;
use App\Models\Inventory;
use App\Observers\InventoryObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.customer', 'layouts.admin', 'welcome', 'customer.*', 'admin.*'], function ($view) {
            $defaults = (object) [
                'brand_name_primary' => 'Gas',
                'brand_name_accent' => 'Go',
                'navbar_logo_url' => asset('images/logo-gasgo.png'),
                'footer_logo_url' => asset('images/logo-gasgo.png'),
                'home_hero_image_url' => null,
                'promo_banner_image_url' => null,
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
            ];

            try {
                if (! Schema::hasTable('homepage_settings')) {
                    $view->with('homepageSettings', $defaults);
                    return;
                }

                $settings = HomepageSetting::singleton();
                $view->with('homepageSettings', $settings);
            } catch (\Throwable $e) {
                $view->with('homepageSettings', $defaults);
            }
        });

        // Register Inventory Observer to sync product.stock with inventory.quantity_on_hand
        Inventory::observe(InventoryObserver::class);
    }
}
