<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'transaction_type',
        'subtotal',
        'discount',
        'delivery_fee',
        'total_amount',
        'customer_name',
        'delivery_address',
        'contact_number',
        'latitude',
        'longitude',
        'payment_method',
        'status',
        'approved_at',
        'notes',
        'is_urgent',
        'estimated_delivery_time',
        'delivered_at',
        'cylinder_returned_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'order_type' => 'string',
            'transaction_type' => 'string',
            'is_urgent' => 'boolean',
            'estimated_delivery_time' => 'datetime',
            'approved_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cylinder_returned_at' => 'datetime',
        ];
    }

    public function getFeeFreeTotalAttribute()
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function getClaimablePointsAttribute(): int
    {
        $productSpend = 0;
        if ($this->relationLoaded('orderItems')) {
            $productSpend = $this->orderItems
                ->filter(function ($item) {
                    return !$item->is_reward;
                })
                ->sum('subtotal');
        }

        if ($productSpend <= 0) {
            $productSpend = max(0, (float) ($this->subtotal - $this->discount));
        }

        return max(0, (int) floor($productSpend / 100));
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function serviceReview()
    {
        return $this->hasOne(ServiceReview::class);
    }

    // ── Cylinder Tracking Helpers ──

    /**
     * Determine if this order involves a new cylinder transaction with exchangeable cylinders.
     */
    public function isNewCylinderTransaction(): bool
    {
        if ($this->transaction_type !== 'new_cylinder') {
            return false;
        }

        return $this->getCylinderItems()->isNotEmpty();
    }

    /**
     * Get order items that are cylinder products.
     */
    public function getCylinderItems()
    {
        $this->loadMissing('orderItems.product');

        return $this->orderItems->filter(function ($item) {
            return $item->product?->isCylinder() && !$item->is_reward;
        });
    }

    /**
     * Total quantity of cylinder items in this order.
     */
    public function getTotalCylinderQuantityAttribute(): int
    {
        return (int) $this->getCylinderItems()->sum('quantity');
    }

    /**
     * Check if cylinder from this order has been marked as returned.
     */
    public function isCylinderReturned(): bool
    {
        if ($this->cylinder_returned_at !== null) {
            return true;
        }

        // Fallback check against stock movements for backward compatibility
        return \App\Models\StockMovement::query()
            ->where('type', 'return')
            ->where('empty_in', '>', 0)
            ->where(function ($q) {
                $q->where('reference', $this->order_number)
                  ->orWhere('notes', 'like', '%returned for ' . $this->order_number . '%');
            })
            ->exists();
    }

    /**
     * Get human-readable cylinder return status.
     * Values: 'not_applicable', 'pending_delivery', 'pending_return', 'returned'
     */
    public function getCylinderReturnStatusAttribute(): string
    {
        if (!$this->isNewCylinderTransaction()) {
            return 'not_applicable';
        }

        if ($this->isCylinderReturned()) {
            return 'returned';
        }

        if ($this->status !== 'delivered') {
            return 'pending_delivery';
        }

        return 'pending_return';
    }

    /**
     * Days elapsed since delivery for unreturned cylinder.
     */
    public function getDaysHeldAttribute(): ?int
    {
        $date = $this->delivered_at ?? $this->created_at;
        if (!$date) {
            return null;
        }

        return (int) max(0, $date->diffInDays(now()));
    }
}

