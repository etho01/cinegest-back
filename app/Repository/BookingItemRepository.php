<?php

namespace App\Repository;

use App\Models\BookingItem;
use Illuminate\Database\Eloquent\Collection;

class BookingItemRepository
{
    /**
     * Create a new booking item
     */
    public function create(array $data): BookingItem
    {
        return BookingItem::create($data);
    }

    /**
     * Create multiple booking items
     */
    public function createMany(int $bookingId, array $items): void
    {
        $bookingItems = [];
        
        foreach ($items as $item) {
            $bookingItems[] = [
                'booking_id' => $bookingId,
                'price_id' => $item['priceId'],
                'price_name' => $item['priceName'],
                'price_amount' => $item['priceAmount'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['priceAmount'] * $item['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        BookingItem::insert($bookingItems);
    }

    /**
     * Get booking items
     */
    public function getByBookingId(int $bookingId): Collection
    {
        return BookingItem::where('booking_id', $bookingId)->get();
    }
}
