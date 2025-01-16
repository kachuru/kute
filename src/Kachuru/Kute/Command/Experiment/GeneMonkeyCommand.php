<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Experiment;

use App\Command\Command;
use Kachuru\Kute\Experiment\GeneMonkey\Organism;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneMonkeyCommand extends Command
{
    public function configure(): void
    {
        $this->setName('experiment:gene-monkey');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target phrase');
        $this->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'Number of iterations');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getArgument('target');

        $iterations = $input->getOption('iterations') ?? 1000;

        $length = strlen($target);

        $organisms = [];
        for ($i = 0; $i < 1000; $i++) {
            $organisms[] = Organism::fromRandom($length);
        }

        $bestFitness = 0;
        for ($i = 1; $i <= $iterations; $i++) {
            $organisms = $this->getNextGeneration($organisms, $target);
            $fittest = null;
            foreach ($organisms as $organism) {
                if (is_null($fittest) || $fittest->getFitness() < $organism->getFitness()) {
                    $fittest = $organism;
                }
            }
            if ($fittest->getFitness() > $bestFitness) {
                $bestFitness = $fittest->getFitness();
            }
            $output->writeln(sprintf('%4s: [%0.4f] %s', $i, $bestFitness, $fittest));
            if ($fittest->getFitness() >= 1) {
                $output->writeln('This is the fittest organism');
                return self::SUCCESS;
            }
        }

        return self::FAILURE;
    }

    /**
     * @param Organism[] $organisms
     * @param string     $target
     *
     * @return array
     */
    function getNextGeneration(array $organisms, string $target)
    {
        $totalFitness = 0;
        $bestFitness = 0;
        foreach ($organisms as $organism) {
            if ($organism->getFitness() > $bestFitness) {
                $bestFitness = $organism->getFitness();
            }

            $totalFitness += $organism->getFitness();
        }

        $nextGen = [];
        for ($i = 0; $i < 1000; $i++) {
            $mum = $this->getRandomParent($organisms, $totalFitness);
            $dad = $this->getRandomParent($organisms, $totalFitness);
            $child = $mum->breedWith($dad);
            $child->setFitness($this->calculateFitness($target, $child));
            $nextGen[] = $child;
        }
        return $nextGen;
    }

    function getRandomParent(array $organisms, $totalFitness): Organism
    {
        $rouletteWheelValue = $totalFitness * mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
        foreach ($organisms as $organism) {
            $rouletteWheelValue -= $organism->getFitness();
            if ($rouletteWheelValue < 0) {
                return $organism;
            }
        }
        return $organism;
    }

    function calculateFitness(string $target, Organism $organism): float
    {
        $fitValue = levenshtein($target, $organism->getGenotype()) / strlen($target);
        return  1 - $fitValue;
    }
}