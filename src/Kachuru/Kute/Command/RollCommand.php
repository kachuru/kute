<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RollCommand extends Command
{
    protected function configure()
    {
        $this->setName('roll');
        $this->setDescription('Roll a dice.');
        $this->addArgument('sides', InputArgument::OPTIONAL, 'Number of sides the dice should have');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Result: %d', mt_rand(1, ($input->getArgument('sides') ?? 6))));

        return 0;
    }
}
