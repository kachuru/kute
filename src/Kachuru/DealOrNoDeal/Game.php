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

        foreach ($prizes as $index => $value) {
            $this->boxes[] = new Box($index+1, $value);
        }

        shuffle($this->boxes);
    }

    public function draw(): Box
    {
        return array_shift($this->boxes);
    }

    public function bankerOffer(Box $playerBox): int
    {
        $total = array_reduce(
            $this->boxes,
            function ($carry, Box $box) {
                return $carry + $box->getValue();
            }
        );

        $total += $playerBox->getValue();

        $average = $total / (count($this->boxes)+1);

        $bias = $average * 0.6;

        if ($bias < 1) {
            return 1;
        }

        if ($bias < 10) {
            return (int)$bias;
        }

        return (int)round($bias, -1);
    }
}
