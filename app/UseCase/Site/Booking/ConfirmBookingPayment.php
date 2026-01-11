<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class ConfirmBookingPayment
{
    private BookingRepository $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Confirm booking payment and send notification
     */
    public function handle(int $bookingId): ?Booking
    {
        $booking = $this->bookingRepository->findWithRelations($bookingId, [
            'user',
            'session.cinema',
            'session.movie',
            'session.room',
            'items'
        ]);

        if (!$booking) {
            Log::warning('Booking not found for payment confirmation', [
                'booking_id' => $bookingId,
            ]);
            return null;
        }

        // Mark booking as paid
        $booking->markAsPaid();

        Log::info('Booking marked as paid', [
            'booking_id' => $booking->id,
            'total_tickets' => $booking->total_tickets,
            'session_id' => $booking->session_id,
        ]);

        // Send confirmation email
        $this->sendConfirmationEmail($booking);

        return $booking;
    }

    /**
     * Send confirmation email to customer
     */
    private function sendConfirmationEmail(Booking $booking): void
    {
        try {
            Mail::to($booking->user->email)->send(new BookingConfirmation($booking));

            Log::info('Confirmation email sent', [
                'booking_id' => $booking->id,
                'email' => $booking->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
