<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'price_id',
        'price_name',
        'price_amount',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the booking that owns the item
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Calculate subtotal
     */
    public function calculateSubtotal(): float
    {
        return $this->price_amount * $this->quantity;
    }
}
