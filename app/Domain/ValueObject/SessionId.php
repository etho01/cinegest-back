<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class SessionId
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Session ID must be positive");
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(SessionId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
