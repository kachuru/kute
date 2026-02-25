<?php

namespace Kachuru\Kute\Command\File\Csv;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MergeCommand extends Command
{
    public function configure()
    {
        $this->setName('file:csv:merge');
        $this->addArgument('csv-1', InputArgument::REQUIRED, 'CSV source');
        $this->addArgument('csv-2', InputArgument::REQUIRED, 'CSV source');
        $this->addArgument('mergeField', InputArgument::REQUIRED, 'Merge field');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $csv1 = $input->getArgument('csv-1');
        $csv2 = $input->getArgument('csv-2');

        $data = $this->mergeData($this->readCsv($csv1), $this->readCsv($csv2), $input->getArgument('mergeField'));

        print_r($data);

        return 0;
    }

    private function readCsv($csv): array
    {
        $fh = fopen($csv, 'r');

        $headers = fgetcsv($fh);

        $data = [];
        while (!feof($fh)) {
            $line = fgetcsv($fh);
            if (is_array($line) && count($line) == count($headers)) {
                $data[] = array_combine($headers, $line);
            }
        }

        return $data;
    }

    private function mergeData($mainData, $mergeData, $mergeField): array
    {
        $index = [];

        foreach ($mainData as $id => $data1) {
            $index[$data1[$mergeField]] = $id;
        }

        foreach ($mergeData as $data2) {
            if (array_key_exists($data2[$mergeField], $index)) {
                $id = $index[$data2[$mergeField]];
                $mergeTo = $mainData[$id];
                $mainData[$id] = $this->smartMerge($mergeTo, $data2);
            }
        }

        return $mainData;
    }

    private function smartMerge($mainData, $mergeData): array
    {
        foreach ($mergeData as $field => $value) {
            if (!empty($value)) {
                $mainData[$field] = $value;
            }
        }

        return $mainData;
    }
}
