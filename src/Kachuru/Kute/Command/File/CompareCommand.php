<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Kachuru\File\Directory;
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
//        $this->addOption('ignore', 'i', InputOption::VALUE_IS_ARRAY, 'Matching paths will be ignored', []);
//        $this->addOption('purge', 'p', InputOption::VALUE_IS_ARRAY, 'Delete matches, regardless of duplicates', []);
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
                if ($this->output->isVerbose()) {
                    $this->output->writeln(sprintf('Hashing [%s] ... %s', $entry->getPath(), $entry->getHash()));
                }

                $this->index[$entry->getHash()][] = $entry;
            }
        }
    }

    private function compareDirectory(Directory $directory): void
    {
        $contents = $directory->getContents();

        foreach ($contents as $entry) {
            if ($entry instanceof Directory) {
                $this->compareDirectory($entry);
                if ($entry->isEmpty()) {
                    $this->output->writeln(sprintf('Directory [%s] is empty', $directory->getPath()));
                }
            } else {
                if (array_key_exists($entry->getHash(), $this->index)) {
                    $delete = $this->input->getOption('delete');
                    if ($delete) {
                        $this->output->writeln(sprintf('Deleting: %s matches %s', $entry->getPath(), implode(', ', $this->index[$entry->getHash()])));
                        $directory->unlink($entry);
                    } else {
                        $this->output->writeln(sprintf('Duplicate found: %s matches %s', $entry->getPath(), implode(', ', $this->index[$entry->getHash()])));
                    }
                }
            }
        }
    }
}
