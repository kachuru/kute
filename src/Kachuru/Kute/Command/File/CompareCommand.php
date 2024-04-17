<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends Command
{
    private array $index = [];
    private InputInterface $input;
    private OutputInterface $output;

    public function configure(): void
    {
        $this->setName('file:compare');
        $this->setDescription('Scan a directory to find duplicate files');
        $this->addArgument('canonicalDirectory', InputArgument::REQUIRED, 'Directory to treat as the canonical reference');
        $this->addArgument('comparisonDirectory', InputArgument::OPTIONAL, 'Directory to scan for duplication');
        $this->addOption('delete', 'd', InputOption::VALUE_NONE, 'Delete duplicates');
        $this->addOption('silent', 's', InputOption::VALUE_NONE, 'Only display errors');
//        $this->addOption('ignore', 'i', InputOption::VALUE_IS_ARRAY, 'Matching paths will be ignored', []);
//        $this->addOption('purge', 'p', InputOption::VALUE_IS_ARRAY, 'Delete matches, regardless of duplicates', []);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $canonicalDirectory = $input->getArgument('canonicalDirectory');
        if (!file_exists($canonicalDirectory) || !is_dir($canonicalDirectory)) {
            throw new \InvalidArgumentException('Please provide a directory');
        }

        $output->writeln(sprintf('Building file index for [%s] ...', $canonicalDirectory));
        $this->buildIndex($canonicalDirectory);

        $output->writeln('Scanning directory for duplicates...');

        return 0;
    }

    private function buildIndex(string $path): void
    {
        $dir = Dir($path);
        while (false !== ($entry = $dir->read())) {
            $fullEntry = $path . $entry;
            if (!file_exists($fullEntry)) {
                throw new \RuntimeException('Critical file failure: ' . $fullEntry);
            }

            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (is_dir($fullEntry)) {
                $this->buildIndex($fullEntry . DIRECTORY_SEPARATOR);
            } else {
                $this->addFileToIndex($fullEntry);
            }
        }
    }

    private function addFileToIndex(string $fullEntry): void
    {
        $sha = sha1_file($fullEntry);
        if (!$this->input->getOption('silent') && array_key_exists($sha, $this->index)) {
            $this->output->writeln(sprintf('Warning when indexing %s: File %s already exists', $fullEntry, $this->index[$sha][0]));
        }

        $this->index[sha1_file($fullEntry)][] = $fullEntry;
    }
}