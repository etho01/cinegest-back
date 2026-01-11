<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\BookingStatus;
use Carbon\Carbon;

final class Booking
{
    private int $id;
    private UserId $userId;
    private SessionId $sessionId;
    private string $paymentIntentId;
    private BookingStatus $status;
    private Money $totalAmount;
    private int $totalTickets;
    private ?Carbon $paidAt;
    private Carbon $createdAt;

    public function __construct(
        int $id,
        UserId $userId,
        SessionId $sessionId,
        string $paymentIntentId,
        BookingStatus $status,
        Money $totalAmount,
        int $totalTickets,
        ?Carbon $paidAt,
        Carbon $createdAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->paymentIntentId = $paymentIntentId;
        $this->status = $status;
        $this->totalAmount = $totalAmount;
        $this->totalTickets = $totalTickets;
        $this->paidAt = $paidAt;
        $this->createdAt = $createdAt;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function paymentIntentId(): string
    {
        return $this->paymentIntentId;
    }

    public function status(): BookingStatus
    {
        return $this->status;
    }

    public function totalAmount(): Money
    {
        return $this->totalAmount;
    }

    public function totalTickets(): int
    {
        return $this->totalTickets;
    }

    public function paidAt(): ?Carbon
    {
        return $this->paidAt;
    }

    public function createdAt(): Carbon
    {
        return $this->createdAt;
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function markAsPaid(Carbon $paidAt): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->sessionId,
            $this->paymentIntentId,
            BookingStatus::paid(),
            $this->totalAmount,
            $this->totalTickets,
            $paidAt,
            $this->createdAt
        );
    }

    public function cancel(): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->sessionId,
            $this->paymentIntentId,
            BookingStatus::cancelled(),
            $this->totalAmount,
            $this->totalTickets,
            $this->paidAt,
            $this->createdAt
        );
    }
}
