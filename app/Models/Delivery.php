<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rider_id',
        'status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'proof_photo',
        'delivery_notes',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getProofPhotoUrlAttribute(): ?string
    {
        if (blank($this->proof_photo)) {
            return null;
        }

        $path = ltrim($this->proof_photo, '/');

        if (filter_var($this->proof_photo, FILTER_VALIDATE_URL)) {
            return $this->proof_photo;
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

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
