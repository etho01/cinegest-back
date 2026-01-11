<?php

namespace App\Application\DTO;

final class BookingDTO
{
    public int $id;
    public int $userId;
    public int $sessionId;
    public string $status;
    public float $totalAmount;
    public string $currency;
    public int $totalTickets;
    public ?string $paidAt;
    public string $createdAt;

    public function __construct(
        int $id,
        int $userId,
        int $sessionId,
        string $status,
        float $totalAmount,
        string $currency,
        int $totalTickets,
        ?string $paidAt,
        string $createdAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->status = $status;
        $this->totalAmount = $totalAmount;
        $this->currency = $currency;
        $this->totalTickets = $totalTickets;
        $this->paidAt = $paidAt;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'sessionId' => $this->sessionId,
            'status' => $this->status,
            'totalAmount' => $this->totalAmount,
            'currency' => $this->currency,
            'totalTickets' => $this->totalTickets,
            'paidAt' => $this->paidAt,
            'createdAt' => $this->createdAt,
        ];
    }
}
