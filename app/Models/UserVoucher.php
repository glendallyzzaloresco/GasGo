<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_name',
        'discount_amount',
        'description',
        'unlocked_at',
        'expires_at',
        'applied_at',
        'order_id',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'expires_at' => 'datetime',
            'applied_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    // ── Helpers ──

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isDaysUntilExpiry()
    {
        if ($this->isExpired()) {
            return 0;
        }
        return now()->diffInDays($this->expires_at);
    }
}
