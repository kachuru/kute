<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Kachuru\Kute\File\Directory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends Command
{
    const UNIX_TRAVERSE_DIRECTORIES = ['.', '..'];
    const HASH_ALGORITHM = 'sha256';

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
            throw new \InvalidArgumentException('Please provide a directory');
        }

        $output->writeln(sprintf('Building file index for [%s] ...', $canonicalDirectory));
        $this->buildIndex($canonicalDirectory);

        $comparisonDirectory = $input->getArgument('comparisonDirectory');
        if (!empty($comparisonDirectory)) {
            $output->writeln('Scanning directory for duplicates...');
            $this->compareDirectory($comparisonDirectory);
        }

        return self::SUCCESS;
    }

    private function buildIndex(string $path): void
    {
        $directory = new Directory($path);
        $contents = $directory->getContents();
        print_r($directory);
        die('boo!');
        $dir = Dir($path);
        while (false !== ($entry = $dir->read())) {
            $fullEntry = $path . $entry;
            if (!is_link($fullEntry) && !file_exists($fullEntry)) {
                throw new \RuntimeException('Critical file failure: ' . $fullEntry);
            }

            if (in_array($entry, self::UNIX_TRAVERSE_DIRECTORIES)) {
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
        // FIXME: If the reference is a link to a non-existent file the sha1_file reference fails.
        //        The $fullEntry won't match for a link since the path prefixes are different. This needs to be updated
        //        so that it can get the relative path.
        $sha = is_link($fullEntry)
            ? hash(self::HASH_ALGORITHM, $fullEntry)
            : hash_file(self::HASH_ALGORITHM, $fullEntry);

        if ($this->input->getOption('verbose') && array_key_exists($sha, $this->index)) {
            $this->output->writeln(sprintf('Warning: "%s" <= File "%s" already exists', $fullEntry, $this->index[$sha][0]));
        }

        $this->index[$sha][] = $fullEntry;
    }

    private function compareDirectory(string $path): void
    {
        $dir = Dir($path);
        while (false !== ($entry = $dir->read())) {
            $fullEntry = $path . $entry;
            if (!is_link($fullEntry) && !file_exists($fullEntry)) {
                throw new \RuntimeException('Critical file failure: ' . $fullEntry);
            }

            if (in_array($entry, self::UNIX_TRAVERSE_DIRECTORIES)) {
                continue;
            }

            if (is_dir($fullEntry)) {
                $this->compareDirectory($fullEntry . DIRECTORY_SEPARATOR);
            } else {
                $this->compareFile($fullEntry);
            }
        }
    }

    private function compareFile(string $fullEntry): void
    {
        $sha = is_link($fullEntry)
            ? hash(self::HASH_ALGORITHM, $fullEntry)
            : hash_file(self::HASH_ALGORITHM, $fullEntry);

        if (array_key_exists($sha, $this->index)) {
            $this->output->writeln(sprintf('%s is a duplicate of %s', $fullEntry, $this->index[$sha][0]));

            if ($this->input->getOption('delete')) {
                if (unlink($fullEntry)) {
                    $this->output->writeln('... Deleted!');
                } else {
                    $this->output->writeln('Failed to delete file');
                }
            }
        }
    }
}