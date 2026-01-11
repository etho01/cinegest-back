<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Entity\Booking as BookingEntity;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\BookingStatus;
use App\Models\Booking as BookingModel;

class BookingMapper
{
    /**
     * Map Eloquent Model to Domain Entity
     */
    public static function toDomainEntity(BookingModel $model): BookingEntity
    {
        return new BookingEntity(
            $model->id,
            new UserId($model->user_id),
            new SessionId($model->session_id),
            $model->payment_intent_id,
            BookingStatus::fromString($model->status),
            new Money($model->total_amount, $model->currency),
            $model->total_tickets,
            $model->paid_at,
            $model->created_at
        );
    }

    /**
     * Map Domain Entity to Eloquent Model attributes
     */
    public static function toEloquentAttributes(BookingEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'user_id' => $entity->userId()->value(),
            'session_id' => $entity->sessionId()->value(),
            'payment_intent_id' => $entity->paymentIntentId(),
            'status' => $entity->status()->value(),
            'total_amount' => $entity->totalAmount()->amount(),
            'currency' => $entity->totalAmount()->currency(),
            'total_tickets' => $entity->totalTickets(),
            'paid_at' => $entity->paidAt(),
        ];
    }
}
