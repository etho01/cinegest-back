<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\MovieId;

final class Movie
{
    private MovieId $id;
    private string $externalId;
    private string $title;
    private int $status;

    public function __construct(
        MovieId $id,
        string $externalId,
        string $title,
        int $status
    ) {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->title = $title;
        $this->status = $status;
    }

    public function id(): MovieId
    {
        return $this->id;
    }

    public function externalId(): string
    {
        return $this->externalId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
