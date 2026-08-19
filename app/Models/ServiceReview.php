<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'rider_id',
        'rating',
        'comment',
        'service_tags',
        'is_featured',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'service_tags' => 'array',
            'is_featured' => 'boolean',
            'is_approved' => 'boolean',
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

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    // ── Scopes ──

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_approved', true)->where('is_featured', true);
    }
}
