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
        if ($this->image) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }

            $normalized = ltrim($this->image, '/');

            if (str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            return \Illuminate\Support\Facades\Storage::url($normalized);
        }
        return asset('images/default-product.png');
    }
}
