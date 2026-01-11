<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class BookingStatus
{
    private const PENDING = 'pending';
    private const PAID = 'paid';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::PAID,
        self::CANCELLED,
    ];

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid booking status: {$value}");
        }

        $this->value = $value;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function equals(BookingStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
