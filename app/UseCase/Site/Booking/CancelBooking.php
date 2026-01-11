<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class CancelBooking
{
    private BookingRepository $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Cancel a booking
     */
    public function handle(int $bookingId): ?Booking
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            Log::warning('Booking not found for cancellation', [
                'booking_id' => $bookingId,
            ]);
            return null;
        }

        $booking->markAsCancelled();

        Log::info('Booking cancelled', [
            'booking_id' => $booking->id,
            'session_id' => $booking->session_id,
        ]);

        return $booking;
    }
}
