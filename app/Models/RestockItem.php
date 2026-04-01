<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockItem extends Model
{
    use HasFactory;

    protected $table = 'restock_items';

    protected $fillable = [
        'restock_id',
        'product_id',
        'quantity',
    ];

    /**
     * Get the restock for this item.
     */
    public function restock()
    {
        return $this->belongsTo(Restock::class);
    }

    /**
     * Get the product for this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
