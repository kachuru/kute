<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Kachuru\Util\Dice;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RollCommand extends Command
{
    private Dice $dice;

    public function __construct(Dice $dice)
    {
        $this->dice = $dice;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('roll');
        $this->setDescription('Roll a dice.');
        $this->addArgument('format', InputArgument::OPTIONAL, 'The format of the dice to roll, e.g.: d6, 2d4, 3d5+3');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            sprintf(
                'Rolled %s and got %d',
                $input->getArgument('format'),
                $this->dice->roll($input->getArgument('format'))
            )
        );

        return self::SUCCESS;
    }
}
