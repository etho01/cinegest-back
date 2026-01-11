<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use Illuminate\Database\Eloquent\Collection;

class GetUserBookings
{
    /**
     * Get all bookings for a user
     */
    public static function handle(int $userId, ?string $status = null): Collection
    {
        $bookingRepository = new BookingRepository();
        
        return $bookingRepository->getUserBookings($userId, $status);
    }
}
