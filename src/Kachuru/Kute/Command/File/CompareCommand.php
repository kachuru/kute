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

        $canonicalDirectory = $this->appendTrailingSlash($input->getArgument('canonicalDirectory'));
        if (!file_exists($canonicalDirectory) || !is_dir($canonicalDirectory)) {
            throw new \InvalidArgumentException('Please provide a directory');
        }

        $output->writeln(sprintf('Building file index for [%s] ...', $canonicalDirectory));
        $this->buildIndex($canonicalDirectory);

        $compareDirectory = $input->getArgument('comparisonDirectory');
        if (!is_null($compareDirectory)) {
            $compareDirectory = $this->appendTrailingSlash($compareDirectory);
            $output->writeln(sprintf('Scanning for duplicates in [%s]...', $compareDirectory));

            $this->scanDirectory($compareDirectory);
        }

        return self::SUCCESS;
    }

    private function buildIndex(string $path): void
    {
        $this->recurseDirectoryWithFileAction(
            $path,
            function (string $fullEntry) {
                $this->addFileToIndex($fullEntry);
            }
        );
    }

    private function scanDirectory(string $directory): void
    {
        $this->recurseDirectoryWithFileAction(
            $directory,
            function (string $fullEntry): void {
                $this->checkFileInIndex($fullEntry);
            }
        );
    }

    private function recurseDirectoryWithFileAction(string $path, callable $fileAction): void
    {
        $dir = Dir($path);
        while (false !== ($entry = $dir->read())) {
            $filename = $path . $entry;
            if (!file_exists($filename)) {
                throw new \RuntimeException('Critical file failure: ' . $filename);
            }

            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (is_dir($filename)) {
                $this->recurseDirectoryWithFileAction($filename . DIRECTORY_SEPARATOR, $fileAction);
            } else {
                $fileAction($filename);
            }
        }
    }

    private function addFileToIndex(string $filename): void
    {
        $sha = $this->getHash($filename);
        if (!$this->input->getOption('silent') && array_key_exists($sha, $this->index)) {
            $this->output->writeln(sprintf('Warning when indexing %s: File %s already exists', $filename, $this->index[$sha][0]));
        }

        $this->index[$sha][] = $filename;
    }

    private function checkFileInIndex(string $filename): void
    {
        $fileHash = $this->getHash($filename);
        if (array_key_exists($fileHash, $this->index)) {
            $this->output->writeln(sprintf('File "%s" is duplicated by: "%s"', $this->index[$fileHash][0], $filename));

            if ($this->input->getOption('delete')) {
                unlink($filename);
            }
        }
    }


    private function appendTrailingSlash(string $directory): string
    {
        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        return $directory;
    }

    private function getHash(string $fullEntry): string
    {
        return sha1_file($fullEntry);
    }
}