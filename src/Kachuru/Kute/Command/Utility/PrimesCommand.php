<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Utility;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrimesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('utility:primes');
        $this->setAliases(['primes']);
        $this->setDescription('Generate a number of primes');
        $this->addArgument('count', InputArgument::OPTIONAL, 'Number of primes to generate', 100);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $input->getArgument('count');

        $primes = [2];
        while (count($primes) < $count) {
            $testNum = $primes[count($primes) - 1] + 1;
            while (true) {
                $isPrime = true;
                foreach ($primes as $prime) {
                    if ($testNum % $prime == 0) {
                        $isPrime = false;
                        break;
                    }
                }
                if ($isPrime) {
                    $primes[] = $testNum;
                    $output->writeln((string)$testNum);
                    break;
                }
                $testNum++;
            }
        }

        return self::SUCCESS;
    }
}
