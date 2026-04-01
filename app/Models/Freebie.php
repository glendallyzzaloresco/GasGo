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
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return asset('storage/' . $this->image);
        }
        return asset('images/default-product.png');
    }
}
