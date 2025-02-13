<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Log;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrelateCommand extends Command
{
    private const DATE_MATCHER = '#(?P<date>[0-9]{2,4}[-/]([0-9]{2}|[A-Za-z]{3})[-/][0-9]{2,4}[\sT:][0-9]{2}:[0-9]{2}:[0-9]{2})#';
    private array $base = [];

    public function configure(): void
    {
        $this->setName('log:correlate');
        $this->setDescription('Correlate two logs together');
        $this->addArgument('baseLogFile', InputArgument::REQUIRED, 'File will be used as the base');
        $this->addArgument(
            'matchLogFile',
            InputArgument::REQUIRED,
            'Entries in file will be matched to entries in the base'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseFile = strval($input->getArgument('baseLogFile'));
        $matchFile = strval($input->getArgument('matchLogFile'));

        if (!file_exists($baseFile) || !file_exists($matchFile)) {
            $output->writeln('<error>Please provide files that exist, thx</error>');
            return self::FAILURE;
        }

        $this->loadBaseFile($baseFile, $output);

        if (!empty($this->base)) {
            $this->correlateMatchFile($matchFile, $output);
        }

        return self::SUCCESS;
    }

    private function loadBaseFile(string $baseFile, OutputInterface $output): void
    {
        $baseDataFh = fopen($baseFile, 'r');
        if (!is_resource($baseDataFh)) {
            throw new \RuntimeException('Unable to read base file');
        }

        while (false !== ($line = fgets($baseDataFh))) {
            $date = $this->matchDate($line);
            if (is_null($date)) {
                $output->writeln('<error>Failed to extract date from entry in base log file</error>');
            } else {
                $this->base[$date->getTimestamp()][] = [
                    'date' => $date,
                    'line' => trim($line),
                ];
            }
        }
    }

    private function correlateMatchFile(string $matchFile, OutputInterface $output): void
    {
        $matchDataFh = fopen($matchFile, 'r');
        if (!is_resource($matchDataFh)) {
            throw new \RuntimeException('Unable to read match file');
        }

        while (false !== ($line = fgets($matchDataFh))) {
            $date = $this->matchDate($line);
            if (is_null($date)) {
                $output->writeln('<error>Failed to extract date from entry in match log file</error>');
            } else {
                if (array_key_exists($date->getTimestamp(), $this->base)) {
                    $entries = $this->base[$date->getTimestamp()];
                    foreach ($entries as $entry) {
                        $writeLine = sprintf('%s %s', trim($line), $entry['line']);
                        $writeLine = count($entries) > 1
                            ? sprintf('<comment>%s</comment>', $writeLine)
                            : sprintf('<info>%s</info>', $writeLine);
                        $output->writeln($writeLine);
                    }
                }
            }
        }
    }

    private function matchDate(string $line): ?\DateTimeInterface
    {
        preg_match(self::DATE_MATCHER, $line, $matches);

        if (!array_key_exists('date', $matches)) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('d/M/Y:H:i:s', $matches['date']);
        if (false === $date) {
            $date = new \DateTimeImmutable($matches['date']);
        }

        return $date;
    }
}
