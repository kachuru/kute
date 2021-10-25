<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Time;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimestampCommand extends Command
{
    public function configure()
    {
        $this->setName('time:dt2ts');
        $this->setAliases(['dt2ts', 'timestamp']);
        $this->setDescription('Display the epoch timestamp of the provided datetime (or the current timestamp)');
        $this->addArgument(
            'datetime',
            InputArgument::OPTIONAL,
            'The datetime (or any other compatible format) to parse'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln((new \DateTime($input->getArgument('datetime')))->format('U'));

        return 0;
    }
}
