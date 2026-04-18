<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'cost_price',
        'selling_price',
        'stock',
        'weight',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // ── Accessors & Helpers ──

    /**
     * Get the quantity on hand from inventory
     */
    public function getQuantityOnHandAttribute()
    {
        return $this->inventory?->quantity_on_hand ?? 0;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock()
    {
        return $this->inventory && $this->inventory->quantity_on_hand > 0 && $this->inventory->status === 'active' && !$this->inventory->isExpired();
    }

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        return $this->inventory && $this->inventory->isLow();
    }

    /**
     * Resolve product image path strictly from DB value.
     */
    public function getResolvedImageAttribute()
    {
        $path = $this->image;
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        if (str_starts_with($normalized, 'images/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }

    /**
     * Get the full URL for the product image.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            if (str_starts_with($this->image, '/')) {
                return asset($this->image);
            }
            return asset('storage/' . $this->image);
        }
        return asset('images/default-product.png');
    }
}
