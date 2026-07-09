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
        'full_in',
        'full_out',
        'empty_in',
        'empty_out',
        'type',
        'reference',
        'notes',
        'created_by',
        'movement_date',
    ];

    protected $casts = [
        'full_in' => 'integer',
        'full_out' => 'integer',
        'empty_in' => 'integer',
        'empty_out' => 'integer',
        'movement_date' => 'datetime',
    ];

    /**
     * Backward-compatible net quantity accessor used by existing views/reports.
     */
    public function getQuantityChangeAttribute(): int
    {
        return (int) $this->full_in - (int) $this->full_out + (int) $this->empty_in - (int) $this->empty_out;
    }

    /**
     * Backward-compatible mutator for older create/update calls.
     */
    public function setQuantityChangeAttribute($value): void
    {
        $change = (int) $value;
        $this->attributes['full_in'] = $change > 0 ? $change : 0;
        $this->attributes['full_out'] = $change < 0 ? abs($change) : 0;
        $this->attributes['empty_in'] = 0;
        $this->attributes['empty_out'] = 0;
    }

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
