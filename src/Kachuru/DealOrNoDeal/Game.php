<?php

namespace Kachuru\DealOrNoDeal;

class Game
{
    private array $boxes;

    private Box $playerBox;

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

    public function __construct(private readonly BankerOffer $bankerOffer) {
    }

    public function init(): self
    {
        $prizes = self::PRIZES;
        shuffle($prizes);

        foreach ($prizes as $index => $value) {
            $this->boxes[] = new Box($index+1, $value);
        }

        shuffle($this->boxes);

        return $this;
    }

    public function draw(): Box
    {
        $box = array_pop($this->boxes);

        if (!isset($this->playerBox)) {
            $this->playerBox = $box;
        }

        return $box;
    }

    /** @return float[] */
    public function remainingPrizes(): array
    {
        $prizes = array_map(
            function (Box $box) {
                return $box->getValue();
            },
            $this->boxes
        );
        $prizes[] = $this->playerBox->getValue();

        return $prizes;
    }

    public function bankerOffer(): int
    {
        return $this->bankerOffer->calculate($this->remainingPrizes());
    }
}
