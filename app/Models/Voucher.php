<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'discount_amount',
        'reward_points_required',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    // Check if voucher is available
    public function isAvailable(): bool
    {
        return $this->is_active && 
               (!$this->expires_at || $this->expires_at > now());
    }

    // Check if customer can claim this voucher
    public function canClaimBy($loyaltyPoints): bool
    {
        return $this->isAvailable() && $loyaltyPoints >= $this->reward_points_required;
    }
}
