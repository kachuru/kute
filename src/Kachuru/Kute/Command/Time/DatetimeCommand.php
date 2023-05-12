<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Time;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatetimeCommand extends Command
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function configure(): void
    {
        $this->setName('time:ts2dt');
        $this->setAliases(['ts2dt', 'datetime']);
        $this->setDescription('Display the datetime of the provided timestamp (or the current datetime)');
        $this->addArgument(
            'timestamp',
            InputArgument::OPTIONAL,
            'The timestamp (or any other compatible format) to parse'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            \DateTime::createFromFormat('U', $input->getArgument('timestamp'))
                ->format(self::DATETIME_FORMAT)
        );

        return self::SUCCESS;
    }
}
