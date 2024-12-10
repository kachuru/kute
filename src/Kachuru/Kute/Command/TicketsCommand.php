<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TicketsCommand extends Command
{
    private const TICKET_FORMAT_PREG = '/^[a-f0-9]+ (\[)?(?P<ticknum>[A-Z0-9]+\-[0-9]+)(\])?[\:\- ](?P<detail>.*)$/';

    public function configure(): void
    {
        $this->setName('tickets');

        $this->addOption(
            'separator',
            's',
            InputOption::VALUE_OPTIONAL,
            'Provide the list of tickets separated by this',
            PHP_EOL
        );

        $this->addOption(
            'detailed',
            'd',
            InputOption::VALUE_NONE,
            'Show a detailed list of commits'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $separator = $input->getOption('separator');
        $detailed = $input->getOption('detailed');

        $snips = [];
        while (false !== ($line = fgets(STDIN))) {
            if (preg_match(self::TICKET_FORMAT_PREG, $line, $match)) {
                $snips[] = trim($match['ticknum']) . ($detailed ? ': ' . trim($match['detail']) : '');
            }
        }

        natsort($snips);
        if (!$detailed) {
            $snips = array_unique($snips);
        }

        $output->writeln(implode($separator, $snips));

        return self::SUCCESS;
    }
}
