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
