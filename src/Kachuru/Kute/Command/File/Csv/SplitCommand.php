<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File\Csv;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SplitCommand extends Command
{
    public function configure()
    {
        $this->setName('file:csv:split');
        $this->addArgument('filename', InputArgument::REQUIRED, 'CSV file to split');
        $this->addOption('files', 'f', InputOption::VALUE_OPTIONAL, 'Number of files to split into', 2);
        // $this->addOption('rows', 'r', InputOption::VALUE_OPTIONAL, 'Number of rows per file');
        $this->addOption('preserve-headers', 'p', InputOption::VALUE_NONE, 'Preserve headers');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" could not be found', $filename));
        }

        $numRows = $this->getNumberOfRows($filename, $input->getOption('preserve-headers'));
        $numRowsPerFile = ceil($numRows / $input->getOption('files'));

        $fhr = fopen($filename, 'r');

        $headers = $input->getOption('preserve-headers')
            ? fgetcsv($fhr)
            : [];

        $filenames = $this->generateOutputFilenames($filename, (int) $input->getOption('files'));

        foreach ($filenames as $filename) {
            $fhw = fopen($filename, 'w+');

            if ($input->getOption('preserve-headers')) {
                fputcsv($fhw, $headers);
            }

            $row = 0;
            while (!feof($fhr) && $row < $numRowsPerFile) {
                fputcsv($fhw, fgetcsv($fhr));
                $row++;
            }

            fclose($fhw);
        }

        fclose($fhr);

        return 0;
    }

    private function generateOutputFilenames(string $filename, int $numFiles): iterable
    {
        $fileinfo = pathinfo($filename);
        foreach (range(1, $numFiles) as $i) {
            yield sprintf('%s.%s.%s', $fileinfo['filename'], $i, $fileinfo['extension']);
        }
    }

    private function getNumberOfRows(string $filename, bool $preserveHeaders): int
    {
        $fh = fopen($filename, 'r');

        if ($preserveHeaders) {
            fgetcsv($fh);
        }

        $numRows = 0;
        while (false !== fgetcsv($fh)) {
            $numRows++;
        }

        fclose($fh);

        return $numRows;
    }
}
