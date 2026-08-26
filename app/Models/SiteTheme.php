<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteTheme extends Model
{
    use HasFactory;

    protected $table = 'site_theme';

    protected $fillable = [
        'primaryColor',
        'accentColor',
        'backgroundColor',
        'sidebarBackground',
        'logoUrl',
        'footerDescription',
        'contactAddress',
        'contactPhone',
    ];

    public $timestamps = false;

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public const UPDATED_AT = 'updated_at';

    public function getLogoUrlAttribute(?string $value): ?string
    {
        try {
            $setting = HomepageSetting::first();
            if ($setting && !empty($setting->navbar_logo_path)) {
                return $setting->navbar_logo_url;
            }
        } catch (\Throwable $e) {
        }

        if (! $value) {
            return asset('images/logo-gasgo.png');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $parsed = parse_url($value);
            $host = $parsed['host'] ?? '';
            if (! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                return $value;
            }
            $value = $parsed['path'] ?? '';
        }

        $normalized = ltrim((string) $value, '/');

        if (str_starts_with($normalized, 'images/')) {
            return asset($normalized);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        if (config('filesystems.default') === 's3') {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($normalized);
        }

        if (file_exists(public_path('storage/' . $normalized)) || file_exists(storage_path('app/public/' . $normalized))) {
            return asset('storage/' . $normalized);
        }

        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        return asset('images/logo-gasgo.png');
    }

    public static function singleton(): self
    {
        $theme = static::query()->first();

        if (! $theme) {
            $theme = static::query()->create([
                'id' => 1,
                'primaryColor' => '#1a6db0',
                'accentColor' => '#f7941d',
                'backgroundColor' => '#f4f7fb',
                'sidebarBackground' => '#111b35',
                'logoUrl' => '/images/logo-gasgo.png',
                'footerDescription' => 'Your trusted partner for fast, reliable LPG delivery. Track your orders in real-time and earn rewards with every purchase.',
                'contactAddress' => 'PNR Site Estacion San Miguel Calasiao Pangasinan',
                'contactPhone' => '+63 912 345 6789',
            ]);
        }

        return $theme;
    }
}
