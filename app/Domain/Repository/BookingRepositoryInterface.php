<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Booking;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\BookingStatus;

interface BookingRepositoryInterface
{
    /**
     * Find booking by ID
     */
    public function findById(int $id): ?Booking;

    /**
     * Find booking by payment intent ID
     */
    public function findByPaymentIntentId(string $paymentIntentId): ?Booking;

    /**
     * Get user bookings
     * 
     * @return Booking[]
     */
    public function getUserBookings(UserId $userId, ?BookingStatus $status = null): array;

    /**
     * Get total tickets sold for a session
     */
    public function getTotalTicketsSold(SessionId $sessionId): int;

    /**
     * Save booking
     */
    public function save(Booking $booking): Booking;
}
