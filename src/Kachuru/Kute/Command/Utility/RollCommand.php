<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Utility;

use App\Command\Command;
use Kachuru\Util\Dice;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollCommand extends Command
{
    public function __construct(
        private readonly Dice $dice
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('utility:roll');
        $this->setDescription('Roll a dice.');
        $this->addArgument('format', InputArgument::OPTIONAL, 'The format of the dice to roll, e.g.: d6, 2d4, 3d5+3');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            sprintf(
                '%d',
                $this->dice->roll($input->getArgument('format') ?? 'd6')
            )
        );

        return self::SUCCESS;
    }
}
