<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'inventory_id',
        'quantity_change',
        'type',
        'reference',
        'notes',
        'created_by',
        'movement_date',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    /**
     * Get the inventory record for this movement.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who created this movement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
