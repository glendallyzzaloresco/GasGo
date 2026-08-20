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
        'is_anonymous',
        'service_tags',
        'is_featured',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_anonymous' => 'boolean',
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

    // ── Accessors ──

    public function getMaskedAuthorNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous Customer';
        }

        $name = $this->user?->name ?? 'Verified Customer';
        $parts = preg_split('/\s+/', trim($name));
        $maskedParts = [];

        foreach ($parts as $part) {
            $len = mb_strlen($part);
            if ($len <= 1) {
                $maskedParts[] = $part . '*';
            } elseif ($len === 2) {
                $maskedParts[] = mb_substr($part, 0, 1) . '*';
            } else {
                $first = mb_substr($part, 0, 1);
                $last = mb_substr($part, -1);
                $starsCount = min(max($len - 2, 2), 4);
                $maskedParts[] = $first . str_repeat('*', $starsCount) . $last;
            }
        }

        return implode(' ', $maskedParts);
    }
}
