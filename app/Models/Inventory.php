<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'quantity_on_hand',
        'empty_on_hand',
        'last_restocked',
        'supplier',
        'status',
        'expiry_date',
    ];

    protected $casts = [
        'last_restocked' => 'datetime',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product that owns this inventory record.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all stock movements for this inventory.
     */
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if inventory is low (below fixed threshold)
     */
    public function isLow()
    {
        return $this->quantity_on_hand <= 5;
    }

    /**
     * Check if inventory is out of stock
     */
    public function isOutOfStock()
    {
        return $this->quantity_on_hand <= 0;
    }

    /**
     * Check if product is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Determine whether a product can track empty cylinders.
     */
    public function supportsEmptyCylinderTracking(): bool
    {
        return $this->product?->isCylinder() ? true : false;
    }

    /**
     * Return the empty cylinder value for display purposes.
     */
    public function getDisplayEmptyOnHandAttribute(): int
    {
        return $this->supportsEmptyCylinderTracking() ? (int) ($this->empty_on_hand ?? 0) : 0;
    }
}
