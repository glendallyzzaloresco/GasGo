<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freebie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'stock',
        'image',
        'category',
        'reward_points_required',
        'redemption_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full URL for the freebie image.
     */
    public function getImageUrlAttribute()
    {
        return $this->resolved_image ?? asset('images/default-product.png');
    }

    public function getResolvedImageAttribute()
    {
        if ($this->image) {
            $path = $this->image;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $parsed = parse_url($path);
                $host = $parsed['host'] ?? '';
                if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                    $path = $parsed['path'] ?? '';
                } else {
                    return $path;
                }
            }

            $normalized = ltrim($path, '/');

            if (str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            if (config('filesystems.default') === 's3') {
                return \Illuminate\Support\Facades\Storage::disk('s3')->url($normalized);
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

        return asset('images/default-product.png');
    }
}
