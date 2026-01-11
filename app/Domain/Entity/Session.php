<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\MovieId;
use App\Domain\ValueObject\SessionId;
use Carbon\Carbon;

final class Session
{
    private SessionId $id;
    private MovieId $movieId;
    private int $cinemaId;
    private int $roomId;
    private Carbon $startTime;

    public function __construct(
        SessionId $id,
        MovieId $movieId,
        int $cinemaId,
        int $roomId,
        Carbon $startTime
    ) {
        $this->id = $id;
        $this->movieId = $movieId;
        $this->cinemaId = $cinemaId;
        $this->roomId = $roomId;
        $this->startTime = $startTime;
    }

    public function id(): SessionId
    {
        return $this->id;
    }

    public function movieId(): MovieId
    {
        return $this->movieId;
    }

    public function cinemaId(): int
    {
        return $this->cinemaId;
    }

    public function roomId(): int
    {
        return $this->roomId;
    }

    public function startTime(): Carbon
    {
        return $this->startTime;
    }

    public function isInFuture(): bool
    {
        return $this->startTime->isFuture();
    }
}
