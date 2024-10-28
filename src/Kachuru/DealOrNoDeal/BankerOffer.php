<?php

declare(strict_types=1);

namespace Kachuru\DealOrNoDeal;

class BankerOffer
{
    /**
     * @param float[] $remainingPrizes
     * @return int
     */
    public function calculate(array $remainingPrizes): int
    {
        $remainingPrizesNum = count($remainingPrizes);

        $sumOfPrizesInPlay = array_reduce(
            $remainingPrizes,
            function ($carry, float $prize) {
                return $carry + pow($prize, 2);
            },
            0
        );

        return intval(sqrt($sumOfPrizesInPlay / $remainingPrizesNum));
    }
}