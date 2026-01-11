<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class UserId
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("User ID must be positive");
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
