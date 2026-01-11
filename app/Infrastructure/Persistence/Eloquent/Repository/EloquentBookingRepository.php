<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\BookingRepositoryInterface;
use App\Domain\Entity\Booking;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\BookingStatus;
use App\Infrastructure\Persistence\Mapper\BookingMapper;
use App\Models\Booking as BookingModel;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function findById(int $id): ?Booking
    {
        $model = BookingModel::find($id);
        
        return $model ? BookingMapper::toDomainEntity($model) : null;
    }

    public function findByPaymentIntentId(string $paymentIntentId): ?Booking
    {
        $model = BookingModel::where('payment_intent_id', $paymentIntentId)->first();
        
        return $model ? BookingMapper::toDomainEntity($model) : null;
    }

    public function getUserBookings(UserId $userId, ?BookingStatus $status = null): array
    {
        $query = BookingModel::where('user_id', $userId->value());
        
        if ($status) {
            $query->where('status', $status->value());
        }
        
        $models = $query->with(['session.movie', 'session.cinema', 'session.movie.cache', 'session.room', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => BookingMapper::toDomainEntity($model))->toArray();
    }

    public function getTotalTicketsSold(SessionId $sessionId): int
    {
        return BookingModel::where('session_id', $sessionId->value())
            ->where('status', 'paid')
            ->sum('total_tickets');
    }

    public function save(Booking $booking): Booking
    {
        $attributes = BookingMapper::toEloquentAttributes($booking);
        
        $model = BookingModel::updateOrCreate(
            ['id' => $attributes['id']],
            $attributes
        );
        
        return BookingMapper::toDomainEntity($model);
    }
}
