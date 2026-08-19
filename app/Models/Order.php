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
