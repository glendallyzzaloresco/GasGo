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
        'requires_exchange',
        'is_active',
        'status',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'requires_exchange' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::saving(function (Product $product) {
            if ($product->category && empty($product->category_id)) {
                $catLower = strtolower(trim((string) $product->category));
                $targetSlug = match ($catLower) {
                    'tank', 'tanks', 'cylinder', 'cylinders' => 'lpg-tanks',
                    'accessories', 'accessory' => 'accessories',
                    'appliances', 'appliance' => 'appliances',
                    default => \Illuminate\Support\Str::slug($catLower),
                };

                $category = Category::where('slug', $targetSlug)
                    ->orWhereRaw('LOWER(name) = ?', [$catLower])
                    ->first();

                if ($category) {
                    $product->category_id = $category->id;
                }
            } elseif ($product->category_id && empty($product->category)) {
                $category = Category::find($product->category_id);
                if ($category) {
                    $product->category = $category->slug ?: strtolower($category->name);
                }
            }
        });
    }

    // ── Relationships ──

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

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
     * Whether this product is a cylinder product.
     */
    public function getIsCylinderAttribute($value): bool
    {
        $name = strtolower(trim((string) ($this->attributes['name'] ?? '')));
        $cat = strtolower(trim((string) ($this->attributes['category'] ?? '')));

        // Exclude accessories, appliances, freebies and non-tank items
        if (in_array($cat, ['accessories', 'appliances', 'appliance', 'freebie'], true) ||
            str_contains($name, 'hanger') ||
            str_contains($name, 'paste') ||
            str_contains($name, 'regulator') ||
            str_contains($name, 'hose') ||
            str_contains($name, 'clamp') ||
            str_contains($name, 'stove') ||
            str_contains($name, 'burner')) {
            return false;
        }

        if (!empty($this->attributes['requires_exchange'])) {
            return true;
        }

        if (!empty($this->attributes['is_cylinder'])) {
            return true;
        }

        if (in_array($cat, ['tank', 'cylinder', 'lpg', 'lpg-tanks', 'tanks', 'cylinders'], true)) {
            return true;
        }

        if (str_contains($name, 'tank') || str_contains($name, 'cylinder') || str_contains($name, 'lpg')) {
            return true;
        }

        return false;
    }

    /**
     * Set the requires_exchange flag.
     * Overrides the isCylinder() fallback if set.
     */
    public function setRequiresExchangeAttribute($value)
    {
        $this->attributes['requires_exchange'] = (bool) $value;
    }

    /**
     * Whether this product is a cylinder product.
     */
    public function isCylinder(): bool
    {
        return (bool) $this->is_cylinder;
    }

    /**
     * Apply the exchange-product filter using requires_exchange when available.
     * Falls back to category/name filter otherwise.
     */
    public function scopeCylinders($query)
    {
        return $query->where(function ($q) {
            $table = $q->getModel()->getTable();
            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'requires_exchange')) {
                $q->where('requires_exchange', true);
            }
            $q->orWhereRaw('LOWER(category) IN (?, ?, ?, ?, ?)', ['tank', 'cylinder', 'lpg', 'lpg-tanks', 'tanks'])
              ->orWhere('name', 'LIKE', '%Tank%')
              ->orWhere('name', 'LIKE', '%Cylinder%');
        })->where(function ($q) {
            $q->whereRaw('LOWER(COALESCE(category, "")) NOT IN (?, ?, ?)', ['accessories', 'appliances', 'freebie'])
              ->where('name', 'NOT LIKE', '%Regulator%')
              ->where('name', 'NOT LIKE', '%Hose%')
              ->where('name', 'NOT LIKE', '%Clamp%')
              ->where('name', 'NOT LIKE', '%Stove%')
              ->where('name', 'NOT LIKE', '%Burner%')
              ->where('name', 'NOT LIKE', '%Hanger%')
              ->where('name', 'NOT LIKE', '%Paste%');
        });
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
            return asset('images/default-product.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

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

    /**
     * Get the full URL for the product image.
     */
    public function getImageUrlAttribute()
    {
        return $this->resolved_image ?? asset('images/default-product.png');
    }
}
