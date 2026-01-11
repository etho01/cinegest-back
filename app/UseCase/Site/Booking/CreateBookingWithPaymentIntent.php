<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use App\Repository\BookingItemRepository;
use App\Models\User;
use App\Models\Booking;
use App\Models\Session;
use App\Exceptions\Site\InsufficientCapacityException;
use Illuminate\Support\Facades\DB;

class CreateBookingWithPaymentIntent
{
    /**
     * Create a booking and initiate payment
     */
    public static function handle(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $bookingRepository = new BookingRepository();
            $bookingItemRepository = new BookingItemRepository();
            
            // Calculate total tickets
            $totalTickets = self::calculateTotalTickets($data['items']);

            // Get session and verify capacity
            $session = Session::with('room')->findOrFail($data['sessionId']);;

            $availableSeats = $session->room->capacity - $bookingRepository->getTotalTicketsSold($data['sessionId']);
            
            if ($availableSeats < $totalTickets) {
                throw new InsufficientCapacityException($totalTickets, $availableSeats);
            }

            // Create booking
            $booking = $bookingRepository->create([
                'user_id' => $data['userId'],
                'session_id' => $data['sessionId'],
                'payment_intent_id' => '', // Will be updated after payment intent creation
                'status' => 'pending',
                'total_amount' => $data['totalAmount'],
                'currency' => 'eur',
                'total_tickets' => $totalTickets,
            ]);

            // Create booking items
            $bookingItemRepository->createMany($booking->id, $data['items']);

            // Get user and create payment intent
            $user = User::findOrFail($data['userId']);
            $amountInCents = (int) ($data['totalAmount'] * 100);

            $payment = $user->pay($amountInCents, [
                'metadata' => [
                    'bookingId' => $booking->id,
                    'sessionId' => $data['sessionId'],
                    'userId' => $data['userId'],
                    'totalAmount' => $data['totalAmount'],
                ],
            ]);

            // Update booking with payment intent ID
            $bookingRepository->update($booking, [
                'payment_intent_id' => $payment->id,
            ]);

            return [
                'booking' => $booking,
                'payment' => $payment,
            ];
        });
    }

    /**
     * Calculate total tickets from items
     */
    private static function calculateTotalTickets(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] ?? 0;
        }
        return $total;
    }
}
