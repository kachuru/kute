<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Log;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{
    private const LINE_PATTERN = '#^(?P<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) \- \- \[(?P<timestamp>\d{2}/[A-Za-z]{3}/\d{4}:\d{2}:\d{2}:\d{2} \+0000)\] (?P<log>.*)#';

    private const FIELDS = [
        'ip' => null,
        'timestamp' => null,
        'log' => null,
    ];

    protected function configure(): void
    {
        $this->setName('log:parse');
        $this->setDescription('Parse a log file');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Log file to parse');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        if (!is_string($filename) || !file_exists($filename)) {
            $output->writeln('<error>Log file not found</error>');
            return self::FAILURE;
        }

        $logsByIp = [];

        $fh = fopen($filename, 'r');
        while (false !== ($row = fgets($fh))) {
            preg_match(self::LINE_PATTERN, $row, $matches);

            $matches['timestamp'] = new \DateTimeImmutable($matches['timestamp']);

            if (!array_key_exists($matches['ip'], $logsByIp)) {
                $logsByIp[$matches['ip']] = [
                    'count' => 1,
                    'logs' => [array_intersect_key($matches, self::FIELDS)]
                ];
            } else {
                $logsByIp[$matches['ip']]['count']++;
                $logsByIp[$matches['ip']]['logs'][] = array_intersect_key($matches, self::FIELDS);
            }
        }

        uasort($logsByIp, function ($a, $b) {
            return $a['count'] <=> $b['count'];
        });

        foreach ($logsByIp as $ip => $log) {
            usort($log['logs'], function ($a, $b) {
                return $a['timestamp']->getTimestamp() <=> $b['timestamp']->getTimestamp();
            });

            $logsByIp[$ip] = $log;
        }

        print_r($logsByIp);

        return self::SUCCESS;
    }
}
