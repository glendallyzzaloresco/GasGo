<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'transaction_reference',
        'paid_at',
        'proof_of_payment',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (blank($this->proof_of_payment)) {
            return null;
        }

        $path = ltrim($this->proof_of_payment, '/');

        if (filter_var($this->proof_of_payment, FILTER_VALIDATE_URL)) {
            return $this->proof_of_payment;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        return asset('storage/' . $path);
    }

    // ── Relationships ──

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
