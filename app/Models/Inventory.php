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
        'reorder_level',
        'last_restocked',
        'supplier',
        'status',
        'expiry_date',
        'batch_number',
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
     * Check if inventory is low (below reorder level)
     */
    public function isLow()
    {
        return $this->quantity_on_hand <= $this->reorder_level;
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
}
