<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use App\Repository\BookingItemRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Exceptions\Site\InsufficientCapacityException;
use Illuminate\Support\Facades\DB;

class CreateBookingWithPaymentIntent
{
    private BookingRepository $bookingRepository;
    private BookingItemRepository $bookingItemRepository;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    public function __construct(
        BookingRepository $bookingRepository,
        BookingItemRepository $bookingItemRepository,
        SessionRepository $sessionRepository,
        UserRepository $userRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->bookingItemRepository = $bookingItemRepository;
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create a booking and initiate payment
     */
    public function handle(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Calculate total tickets
            $totalTickets = $this->calculateTotalTickets($data['items']);

            // Get session and verify capacity
            $session = $this->sessionRepository->findWithRelations($data['sessionId'], ['room']);

            $availableSeats = $session->room->capacity - $this->bookingRepository->getTotalTicketsSold($data['sessionId']);
            
            if ($availableSeats < $totalTickets) {
                throw new InsufficientCapacityException($totalTickets, $availableSeats);
            }

            // Create booking
            $booking = $this->bookingRepository->create([
                'user_id' => $data['userId'],
                'session_id' => $data['sessionId'],
                'payment_intent_id' => '', // Will be updated after payment intent creation
                'status' => 'pending',
                'total_amount' => $data['totalAmount'],
                'currency' => 'eur',
                'total_tickets' => $totalTickets,
            ]);

            // Create booking items
            $this->bookingItemRepository->createMany($booking->id, $data['items']);

            // Get user and create payment intent
            $user = $this->userRepository->find($data['userId']);
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
            $this->bookingRepository->update($booking, [
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
    private function calculateTotalTickets(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] ?? 0;
        }
        return $total;
    }
}
