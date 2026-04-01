<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'product_id',
        'movement_date',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the product for this movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this movement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the related order if this is an order movement.
     */
    public function order()
    {
        if ($this->reference_type === 'order') {
            return Order::find($this->reference_id);
        }
        return null;
    }

    /**
     * Get the related restock if this is a restock movement.
     */
    public function restock()
    {
        if ($this->reference_type === 'restock') {
            return Restock::find($this->reference_id);
        }
        return null;
    }
}
