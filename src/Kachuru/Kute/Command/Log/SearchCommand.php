<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Log;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    private const COMMAND_STRING = 'ssh %s \'zgrep "%s" %s\'';

    public function configure(): void
    {
        $this->setName('log:search');
        $this->setDescription('Search logs across multiple specified servers');
        $this->addArgument('search', InputArgument::REQUIRED, 'Text to search');
        $this->addArgument('files', InputArgument::IS_ARRAY, 'Files to search');
        $this->addOption('server', 's', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Servers to search');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $search = strval($input->getArgument('search'));
        $files = $input->getArgument('files');
        $servers = $input->getOption('server');

        $verbose = $input->getOption('verbose');

        if (empty($files)) {
            $output->writeln('<error>You must provide at least one file to search</error>');
            return self::FAILURE;
        }

        if (empty($servers)) {
            $output->writeln('<error>You must provide at least one server to search</error>');
            return self::FAILURE;
        }

        if ($verbose) {
            $output->writeln(sprintf('Search for: <info>%s</info>', $search));
            $output->writeln(sprintf('in Files: <info>%s</info>', implode(', ', $files)));
            $output->writeln(sprintf('on Servers: <info>%s</info>', implode(', ', $servers)));
            $output->writeln('');
        }

        foreach ($servers as $server) {
            $command = sprintf(self::COMMAND_STRING, $server, $search, implode(' ', $files));
            passthru($command);
        }

        return self::SUCCESS;
    }
}
