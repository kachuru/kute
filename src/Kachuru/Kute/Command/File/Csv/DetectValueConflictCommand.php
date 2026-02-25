<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File\Csv;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DetectValueConflictCommand extends Command
{
    public function configure(): void
    {
        $this->setName('file:csv:detect-value-conflict');
        $this->setDescription('Check for conflicts of values in the given CSVs');
        $this->addArgument(
            'filenames',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Filename to check for file conflicts'
        );
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'Key to check for duplicate values');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $input->getArgument('filenames');

        $key = $input->getOption('key');

        $conflicts = [];
        $map = [];
        foreach ($files as $file) {
            $fh = fopen($file, 'r');

            $headers = $this->fetchLine($fh);

            if (!in_array($key, $headers)) {
                throw new \InvalidArgumentException(sprintf('%s is not present as a header in %s', $key, $file));
            }

            $rowNo = 0;
            while (!feof($fh)) {
                $rowNo++;

                $line = $this->fetchLine($fh);

                if (false !== $line) {
                    $row = array_combine($headers, $line);

                    $value = $row[$key];

                    if (array_key_exists($value, $map)) {
                        if (!array_key_exists($value, $conflicts)) {
                            $conflicts[$value][] = $map[$value];
                        }

                        $conflicts[$value][] = [
                            'file' => $file,
                            'row' => $rowNo,
                            'value' => $value
                        ];
                    }

                    $map[$value] = [
                        'file' => $file,
                        'row' => $rowNo,
                        'value' => $value
                    ];
                }
            }

            fclose($fh);
        }

        print_r($conflicts);

        return self::SUCCESS;
    }

    /**
     * @param resource $fh
     *
     * @return string[]|false
     */
    private function fetchLine($fh): array|false
    {
        return fgetcsv(stream: $fh, escape: "\\");
    }
}
