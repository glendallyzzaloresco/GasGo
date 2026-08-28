<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
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
            if (empty($product->category_id)) {
                $defaultCategory = Category::first();
                if ($defaultCategory) {
                    $product->category_id = $defaultCategory->id;
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
     * Dynamically resolve category slug from Category relation
     */
    public function getCategoryAttribute(): string
    {
        return $this->categoryModel?->slug 
            ?? ($this->categoryModel?->name ? strtolower($this->categoryModel->name) : ($this->attributes['category'] ?? 'tank'));
    }

    /**
     * Mutator to allow setting category by slug/name
     */
    public function setCategoryAttribute($value): void
    {
        if (empty($value)) return;
        $catLower = strtolower(trim((string) $value));
        $targetSlug = match ($catLower) {
            'tank', 'tanks', 'cylinder', 'cylinders' => 'lpg-tanks',
            'accessories', 'accessory' => 'accessories',
            'appliances', 'appliance' => 'appliances',
            default => \Illuminate\Support\Str::slug($catLower),
        };

        $this->attributes['category'] = $targetSlug;

        try {
            $category = Category::where('slug', $targetSlug)
                ->orWhereRaw('LOWER(name) = ?', [$catLower])
                ->first();

            if ($category) {
                $this->attributes['category_id'] = $category->id;
            }
        } catch (\Throwable $e) {
            // In unit tests or environments without DB connection
        }
    }

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
    public function getIsCylinderAttribute($value = null): bool
    {
        $name = strtolower(trim((string) ($this->attributes['name'] ?? '')));
        $cat = strtolower(trim((string) ($this->attributes['category'] ?? '')));

        // Exclude non-exchange items first: appliances, accessories, stoves, burners, regulators, hoses, etc.
        if (in_array($cat, ['accessories', 'appliances', 'appliance', 'kitchen', 'parts', 'meals', 'snacks', 'beverages', 'bilao', 'dispensers', 'freebie'], true)
            || str_contains($name, 'stove') || str_contains($name, 'burner') || str_contains($name, 'regulator') || str_contains($name, 'hose') || str_contains($name, 'clamp')) {
            return false;
        }

        // Explicit exchange flag takes priority for valid container items
        if (!empty($this->attributes['requires_exchange'])) {
            return true;
        }

        if (!empty($this->attributes['is_cylinder'])) {
            return true;
        }

        if (in_array($cat, ['tank', 'cylinder', 'water', 'lpg', 'lpg-tanks', 'tanks', 'cylinders'], true)) {
            return true;
        }

        if (str_contains($name, 'tank') || str_contains($name, 'cylinder') || str_contains($name, '5-gallon') || str_contains($name, 'refill gallon')) {
            return true;
        }

        return false;
    }

    /**
     * Get dynamic category display label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return \App\Services\CategoryService::formatCategoryLabel($this->category ?? 'tank');
    }

    /**
     * Get dynamic category icon class.
     */
    public function getCategoryIconAttribute(): string
    {
        $cat = strtolower((string) ($this->category ?? ''));
        return match ($cat) {
            'water' => 'fas fa-tint',
            'dispensers' => 'fas fa-faucet',
            'meals' => 'fas fa-utensils',
            'snacks' => 'fas fa-burger',
            'beverages' => 'fas fa-mug-hot',
            'bilao' => 'fas fa-bowl-rice',
            'appliances', 'stoves' => 'fas fa-fire-burner',
            'kitchen' => 'fas fa-blender',
            'parts' => 'fas fa-screwdriver-wrench',
            'accessories' => 'fas fa-tools',
            'freebie' => 'fas fa-gift',
            default => 'fas fa-fire',
        };
    }

    /**
     * Get dynamic category color code.
     */
    public function getCategoryColorAttribute(): string
    {
        $cat = strtolower((string) ($this->category ?? ''));
        return match ($cat) {
            'water', 'dispensers' => '#0088cc',
            'meals' => '#e03131',
            'snacks' => '#ff922b',
            'beverages' => '#f06595',
            'bilao' => '#fab005',
            'appliances', 'stoves' => '#0ca678',
            'kitchen' => '#15aabf',
            'parts', 'accessories' => '#f7941d',
            'freebie' => '#e67700',
            default => '#1a6db0',
        };
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
     * Scope products to match the active business niche (LPG, Water Station, Foods, Appliances).
     */
    public function scopeForNiche($query, ?string $nicheKey = null)
    {
        $niche = $nicheKey ?: \App\Services\CategoryService::detectNicheKey();

        return $query->where(function ($q) use ($niche) {
            if ($niche === 'water') {
                $q->where(function ($sub) {
                    $sub->whereIn('category', ['water', 'dispensers', 'accessories', 'pumps', 'bottles'])
                        ->orWhereRaw("LOWER(name) LIKE '%water%' OR LOWER(name) LIKE '%gallon%' OR LOWER(name) LIKE '%dispenser%' OR LOWER(name) LIKE '%purified%'");
                })->where(function ($sub) {
                    $sub->whereNotIn('category', ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'meals', 'snacks', 'beverages', 'bilao'])
                        ->whereRaw("LOWER(name) NOT LIKE '%tank%' AND LOWER(name) NOT LIKE '%cylinder%' AND LOWER(name) NOT LIKE '%lpg%' AND LOWER(name) NOT LIKE '%solane%'");
                });
            } elseif ($niche === 'foods') {
                $q->where(function ($sub) {
                    $sub->whereIn('category', ['meals', 'snacks', 'beverages', 'bilao', 'food', 'drinks'])
                        ->orWhereRaw("LOWER(name) LIKE '%meal%' OR LOWER(name) LIKE '%snack%' OR LOWER(name) LIKE '%drink%' OR LOWER(name) LIKE '%rice%'");
                })->where(function ($sub) {
                    $sub->whereNotIn('category', ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'water', 'dispensers'])
                        ->whereRaw("LOWER(name) NOT LIKE '%tank%' AND LOWER(name) NOT LIKE '%cylinder%' AND LOWER(name) NOT LIKE '%lpg%'");
                });
            } elseif ($niche === 'appliances') {
                $q->where(function ($sub) {
                    $sub->whereIn('category', ['appliances', 'kitchen', 'living', 'parts', 'stoves', 'burners'])
                        ->orWhereRaw("LOWER(name) LIKE '%stove%' OR LOWER(name) LIKE '%burner%' OR LOWER(name) LIKE '%appliance%'");
                })->where(function ($sub) {
                    $sub->whereNotIn('category', ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'water', 'meals', 'snacks', 'beverages', 'bilao'])
                        ->whereRaw("LOWER(name) NOT LIKE '%tank%' AND LOWER(name) NOT LIKE '%cylinder%' AND LOWER(name) NOT LIKE '%lpg%'");
                });
            } else {
                // LPG (default)
                $q->where(function ($sub) {
                    $sub->whereIn('category', ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'accessories', 'appliances', 'stoves', 'burners', 'parts'])
                        ->orWhereRaw("LOWER(name) LIKE '%tank%' OR LOWER(name) LIKE '%cylinder%' OR LOWER(name) LIKE '%lpg%' OR LOWER(name) LIKE '%regulator%' OR LOWER(name) LIKE '%stove%'");
                })->where(function ($sub) {
                    $sub->whereNotIn('category', ['water', 'dispensers', 'meals', 'snacks', 'beverages', 'bilao'])
                        ->whereRaw("LOWER(name) NOT LIKE '%gallon%' AND LOWER(name) NOT LIKE '%purified water%'");
                });
            }
        });
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
            $q->orWhere('category_id', 1)
              ->orWhere('name', 'LIKE', '%Tank%')
              ->orWhere('name', 'LIKE', '%Cylinder%');
        })->where(function ($q) {
            $q->where('name', 'NOT LIKE', '%Regulator%')
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

    /**
     * Get the full URL for the product image.
     */
    public function getImageUrlAttribute()
    {
        return $this->resolved_image ?? asset('images/default-product.png');
    }
}
