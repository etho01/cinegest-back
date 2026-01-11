<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use Illuminate\Database\Eloquent\Collection;

class GetUserBookings
{
    private BookingRepository $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Get all bookings for a user
     */
    public function handle(int $userId, ?string $status = null): Collection
    {
        return $this->bookingRepository->getUserBookings($userId, $status);
    }
}
