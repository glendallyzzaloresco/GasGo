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
        ];
    }

    public function getFeeFreeTotalAttribute()
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function getClaimablePointsAttribute(): int
    {
        $tankSpend = 0;
        if ($this->relationLoaded('orderItems')) {
            $tankSpend = $this->orderItems
                ->filter(function ($item) {
                    if ($item->is_reward) {
                        return false;
                    }
                    if ($item->product) {
                        return $item->product->isCylinder();
                    }
                    $name = strtolower((string) ($item->product_name ?? ''));
                    return (str_contains($name, 'tank') || str_contains($name, 'cylinder') || str_contains($name, 'lpg'))
                        && !str_contains($name, 'regulator')
                        && !str_contains($name, 'hose')
                        && !str_contains($name, 'clamp')
                        && !str_contains($name, 'stove')
                        && !str_contains($name, 'burner');
                })
                ->sum('subtotal');
        }

        if ($tankSpend <= 0) {
            $tankSpend = max(0, (float) ($this->subtotal - $this->discount));
        }

        return max(0, (int) floor($tankSpend / 100));
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
}
