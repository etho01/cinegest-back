<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class Money
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = 'EUR')
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Money amount cannot be negative");
        }

        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function amountInCents(): int
    {
        return (int) ($this->amount * 100);
    }

    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot add money with different currencies");
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function multiply(int $multiplier): Money
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}
