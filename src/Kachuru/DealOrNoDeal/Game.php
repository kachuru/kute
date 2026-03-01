<?php

namespace Kachuru\DealOrNoDeal;

class Game
{
    private array $boxes;

    public const PRIZES = [
        0.01,
        0.10,
        0.50,
        1,
        5,
        10,
        50,
        100,
        250,
        500,
        750,
        1000,
        2000,
        3000,
        4000,
        5000,
        7500,
        10000,
        25000,
        50000,
        75000,
        100000,
    ];

    public function init(): void
    {
        $prizes = self::PRIZES;
        shuffle($prizes);

        foreach ($prizes as $index => $value) {
            $this->boxes[] = new Box($index+1, $value);
        }

        shuffle($this->boxes);
    }

    public function draw(): Box
    {
        return array_shift($this->boxes);
    }

    public function bankerOffer(): int
    {
        return intval(array_reduce(
            $this->boxes,
            function ($carry, Box $box) {
                return $carry + $box->getValue();
            }
        )/count($this->boxes));
    }
}
