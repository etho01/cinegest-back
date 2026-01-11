<?php

namespace App\Domain\ValueObject;

use Carbon\Carbon;

final class DateRange
{
    private Carbon $start;
    private Carbon $end;

    public function __construct(Carbon $start, Carbon $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start date must be before end date");
        }

        $this->start = $start;
        $this->end = $end;
    }

    public function start(): Carbon
    {
        return $this->start;
    }

    public function end(): Carbon
    {
        return $this->end;
    }

    public function contains(Carbon $date): bool
    {
        return $date->isBetween($this->start, $this->end);
    }
}
