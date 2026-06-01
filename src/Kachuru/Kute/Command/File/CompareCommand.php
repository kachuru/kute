<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Kachuru\File\Directory;
use Kachuru\File\Path;
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
        $this->addOption('prune', 'p', InputOption::VALUE_NONE, 'Prune empty directories');
        $this->addOption('ignore', 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Matching paths will be ignored', []);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $canonicalDirectory = $input->getArgument('canonicalDirectory');
        if (!file_exists($canonicalDirectory) || !is_dir($canonicalDirectory)) {
            throw new \InvalidArgumentException('Please provide a directory as the canonical reference');
        }

        $comparisonDirectory = $input->getArgument('comparisonDirectory');
        if (!file_exists($comparisonDirectory) || !is_dir($comparisonDirectory)) {
            throw new \InvalidArgumentException('Please provide a directory as the comparison reference');
        }

        $canonicalDirectory = new Directory($canonicalDirectory);
        $this->buildIndex($canonicalDirectory);

        $output->writeln('Scanning directory for duplicates...');
        $comparisonDirectory = new Directory($comparisonDirectory);
        $this->compareDirectory($comparisonDirectory);

        return self::SUCCESS;
    }

    private function buildIndex(Directory $directory): void
    {
        $this->output->writeln(sprintf('Building index for [%s] ...', $directory->getPath()));

        $contents = $directory->getContents();

        foreach ($contents as $entry) {
            if ($entry instanceof Directory) {
                $this->buildIndex($entry);
            } else {
//                if ($this->output->isVerbose()) {
//                    $this->output->writeln(sprintf('Hashing [%s] ... %s', $entry->getPath(), $entry->getHash()));
//                }

                $this->addToIndex($entry);
            }
        }
    }

    private function compareDirectory(Directory $directory): void
    {
        $delete = $this->input->getOption('delete');
        $prune = $this->input->getOption('prune');

        $contents = $directory->getContents();

        foreach ($contents as $entry) {
            if ($entry instanceof Directory) {
                $this->compareDirectory($entry);
                if ($entry->isEmpty() && $delete && $prune) {
                    $this->output->writeln(sprintf('Pruning empty directory [%s]', $entry->getPath()));
                    $directory->delete($entry);
                }
            } else {
                if (array_key_exists($entry->getHash(), $this->index)) {
                    if ($delete) {
                        $this->output->writeln(sprintf('Deleting: %s matches %s', $entry->getPath(), implode(', ', $this->index[$entry->getHash()])));
                        $directory->delete($entry);
                    } else {
                        $this->output->writeln(sprintf('Duplicate found: %s matches %s', $entry->getPath(), implode(', ', $this->index[$entry->getHash()])));
                    }
                }
            }
        }
    }

    private function addToIndex(Path $entry): void
    {
        $ignore = $this->input->getOption('ignore');
        if (!empty($ignore)) {
            foreach ($ignore as $pattern) {
                if (str_contains($entry->getPath(), $pattern)) {
                    return;
                }
            }
        }

        $this->index[$entry->getHash()][] = $entry;
    }
}
