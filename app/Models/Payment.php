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

        // Prioritize Supabase / S3 public URL when configured
        $cloudUrl = config('filesystems.disks.supabase.url')
            ?: config('filesystems.disks.s3.url')
            ?: env('SUPABASE_STORAGE_URL')
            ?: env('AWS_URL');
        $defaultDisk = config('filesystems.default');

        if (in_array($defaultDisk, ['s3', 'supabase'], true) && $cloudUrl) {
            return rtrim($cloudUrl, '/') . '/' . $path;
        }

        if (in_array($defaultDisk, ['s3', 'supabase'], true)) {
            try {
                return \Illuminate\Support\Facades\Storage::disk($defaultDisk)->url($path);
            } catch (\Throwable $e) {}
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        if ($cloudUrl) {
            return rtrim($cloudUrl, '/') . '/' . $path;
        }

        return asset('storage/' . $path);
    }

    // ── Relationships ──

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
