<?php

namespace Kachuru\DealOrNoDeal;

class Box
{
    public function __construct(
        private readonly int $number,
        private readonly float $value
    ) {
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
