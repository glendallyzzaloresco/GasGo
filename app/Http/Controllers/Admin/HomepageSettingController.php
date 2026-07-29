<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\SiteTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageSettingController extends Controller
{
    public function edit()
    {
        $settings = HomepageSetting::singleton();

        return view('admin.homepage-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = HomepageSetting::singleton();

        $validated = $request->validate([
            'brand_name_primary' => 'required|string|max:50',
            'brand_name_accent' => 'required|string|max:50',
            'hero_title_prefix' => 'nullable|string|max:120',
            'hero_title_highlight' => 'nullable|string|max:120',
            'hero_title_suffix' => 'nullable|string|max:120',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_primary_button_label' => 'nullable|string|max:80',
            'products_section_title' => 'nullable|string|max:120',
            'products_section_subtitle' => 'nullable|string|max:300',
            'products_view_all_label' => 'nullable|string|max:120',
            'promo_title' => 'nullable|string|max:180',
            'promo_subtitle' => 'nullable|string|max:500',
            'promo_button_label' => 'nullable|string|max:80',
            'footer_description' => 'nullable|string|max:1000',
            'contact_address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:80',
            'contact_email' => 'nullable|email|max:120',
            'contact_hours' => 'nullable|string|max:120',
            'primary_color' => 'nullable|string|size:7',
            'accent_color' => 'nullable|string|size:7',
            'background_color' => 'nullable|string|size:7',
            'sidebar_bg_color' => 'nullable|string|size:7',
            'navbar_logo' => 'nullable|image|max:2048',
            'footer_logo' => 'nullable|image|max:2048',
            'home_hero_image' => 'nullable|image|max:4096',
            'promo_banner_image' => 'nullable|image|max:4096',
            'remove_navbar_logo' => 'nullable|boolean',
            'remove_footer_logo' => 'nullable|boolean',
            'remove_home_hero_image' => 'nullable|boolean',
            'remove_promo_banner_image' => 'nullable|boolean',
        ]);

        $payload = [
            'brand_name_primary' => $validated['brand_name_primary'],
            'brand_name_accent' => $validated['brand_name_accent'],
            'hero_title_prefix' => $validated['hero_title_prefix'] ?? null,
            'hero_title_highlight' => $validated['hero_title_highlight'] ?? null,
            'hero_title_suffix' => $validated['hero_title_suffix'] ?? null,
            'hero_subtitle' => $validated['hero_subtitle'] ?? null,
            'hero_primary_button_label' => $validated['hero_primary_button_label'] ?? null,
            'products_section_title' => $validated['products_section_title'] ?? null,
            'products_section_subtitle' => $validated['products_section_subtitle'] ?? null,
            'products_view_all_label' => $validated['products_view_all_label'] ?? null,
            'promo_title' => $validated['promo_title'] ?? null,
            'promo_subtitle' => $validated['promo_subtitle'] ?? null,
            'promo_button_label' => $validated['promo_button_label'] ?? null,
            'footer_description' => $validated['footer_description'] ?? null,
            'contact_address' => $validated['contact_address'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_hours' => $validated['contact_hours'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#1a6db0',
            'accent_color' => $validated['accent_color'] ?? '#f7941d',
            'background_color' => $validated['background_color'] ?? '#f4f7fb',
            'sidebar_bg_color' => $validated['sidebar_bg_color'] ?? '#111b35',
        ];

        if ($request->boolean('remove_navbar_logo')) {
            $this->deletePublicFile($settings->navbar_logo_path);
            $payload['navbar_logo_path'] = null;
        }

        if ($request->boolean('remove_footer_logo')) {
            $this->deletePublicFile($settings->footer_logo_path);
            $payload['footer_logo_path'] = null;
        }

        if ($request->boolean('remove_home_hero_image')) {
            $this->deletePublicFile($settings->home_hero_image_path);
            $payload['home_hero_image_path'] = null;
        }

        if ($request->boolean('remove_promo_banner_image')) {
            $this->deletePublicFile($settings->promo_banner_image_path);
            $payload['promo_banner_image_path'] = null;
        }

        if ($request->hasFile('navbar_logo')) {
            $this->deletePublicFile($settings->navbar_logo_path);
            $payload['navbar_logo_path'] = $request->file('navbar_logo')->store('branding', 'public');
        }

        if ($request->hasFile('footer_logo')) {
            $this->deletePublicFile($settings->footer_logo_path);
            $payload['footer_logo_path'] = $request->file('footer_logo')->store('branding', 'public');
        }

        if ($request->hasFile('home_hero_image')) {
            $this->deletePublicFile($settings->home_hero_image_path);
            $payload['home_hero_image_path'] = $request->file('home_hero_image')->store('branding', 'public');
        }

        if ($request->hasFile('promo_banner_image')) {
            $this->deletePublicFile($settings->promo_banner_image_path);
            $payload['promo_banner_image_path'] = $request->file('promo_banner_image')->store('branding', 'public');
        }

        $settings->update($payload);

        $themePayload = [
            'primaryColor' => $payload['primary_color'] ?? '#1a6db0',
            'accentColor' => $payload['accent_color'] ?? '#f7941d',
            'backgroundColor' => $payload['background_color'] ?? '#f4f7fb',
            'sidebarBackground' => $payload['sidebar_bg_color'] ?? '#111b35',
            'footerDescription' => $payload['footer_description'] ?? null,
            'contactAddress' => $payload['contact_address'] ?? null,
            'contactPhone' => $payload['contact_phone'] ?? null,
        ];

        if (! empty($payload['navbar_logo_path'])) {
            $themePayload['logoUrl'] = Storage::disk('public')->url($payload['navbar_logo_path']);
        }

        SiteTheme::query()->updateOrCreate(['id' => 1], $themePayload);

        return redirect()->route('admin.settings.homepage')->with('success', 'Homepage settings updated successfully.');
    }

    private function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            Storage::disk('public')->delete($path);
        }
    }
}
