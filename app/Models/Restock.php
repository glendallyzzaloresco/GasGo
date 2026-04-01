<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restock extends Model
{
    use HasFactory;

    protected $table = 'restocks';

    protected $fillable = [
        'supplier_name',
        'status',
        'received_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this restock.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the restock items for this restock.
     */
    public function items()
    {
        return $this->hasMany(RestockItem::class);
    }

    /**
     * Get the inventory movements for this restock.
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')->where('reference_type', 'restock');
    }
}
