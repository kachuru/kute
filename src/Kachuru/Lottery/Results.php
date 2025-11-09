<?php

declare(strict_types=1);

namespace Kachuru\Lottery;

class Results
{
    /** @param int[] $numbers */
    public function __construct(
        private readonly \DateTimeInterface $drawDate,
        private readonly array $numbers,
        private readonly int $bonus,
        private readonly int $drawNumber
    ) {
    }

    public function drawDate(): \DateTimeInterface
    {
        return $this->drawDate;
    }

    /** @return int[] */
    public function numbers(): array
    {
        return $this->numbers;
    }

    public function bonus(): int
    {
        return $this->bonus;
    }

    public function drawNumber(): int
    {
        return $this->drawNumber;
    }
}
