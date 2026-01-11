<?php

namespace App\Repository;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Update a booking
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }

    /**
     * Find booking by ID
     */
    public function find(int $id): ?Booking
    {
        return Booking::find($id);
    }

    /**
     * Find booking by payment intent ID
     */
    public function findByPaymentIntentId(string $paymentIntentId): ?Booking
    {
        return Booking::where('payment_intent_id', $paymentIntentId)->first();
    }

    /**
     * Find booking with relationships
     */
    public function findWithRelations(int $id, array $relations = []): ?Booking
    {
        return Booking::with($relations)->find($id);
    }

    /**
     * Get user bookings
     */
    public function getUserBookings(int $userId, string $status = null): Collection
    {
        $query = Booking::where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->with(['session.movie', 'session.cinema', 'session.movie.cache', 'session.room', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get session bookings
     */
    public function getSessionBookings(int $sessionId, string $status = null): Collection
    {
        $query = Booking::where('session_id', $sessionId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->with(['user', 'items'])->get();
    }

    /**
     * Get total tickets sold for a session
     */
    public function getTotalTicketsSold(int $sessionId): int
    {
        return Booking::where('session_id', $sessionId)
            ->where('status', 'paid')
            ->sum('total_tickets');
    }
}
