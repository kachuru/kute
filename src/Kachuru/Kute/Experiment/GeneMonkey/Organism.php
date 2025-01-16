<?php

declare(strict_types=1);

namespace Kachuru\Kute\Experiment\GeneMonkey;

class Organism
{
    private float $fitness = 0;

    public function __construct(
        private readonly string $genotype
    ) {
    }

    public static function fromRandom(int $length): Organism
    {
        $genotype = '';
        do {
            $genotype .= self::getRandomCharacter();
        } while (--$length);

        return new self($genotype);
    }

    public function getFitness(): float
    {
        return $this->fitness;
    }

    public function setFitness(float $fitness): void
    {
        $this->fitness = $fitness;
    }

    public function getGenotype(): string
    {
        return $this->genotype;
    }

    public function breedWith(Organism $partner): Organism {
        $genotype = array_map(
            function($chr1, $chr2) {
                $return = '';

                if (mt_rand(0, 99) === 0) {
                    switch (mt_rand(0, 20)) {
                        case 0:
                            return $return;

                        case 1:
                            $return = self::getRandomCharacter();
                            break;

                        default:
                            return self::getRandomCharacter();
                    }
                }

                return $return . (mt_rand(0, 1) ? $chr1 : $chr2);
            },
            str_split($this->genotype),
            str_split($partner->genotype)
        );

        return new Organism(implode('', $genotype));
    }

    private static function getRandomCharacter(): string
    {
        $chr = chr(mt_rand(97, 123));
        return ($chr === '{') ? ' ' : $chr;
    }

    function __toString()
    {
        return sprintf('%s [%0.4f]', $this->genotype, $this->getFitness());
    }
}