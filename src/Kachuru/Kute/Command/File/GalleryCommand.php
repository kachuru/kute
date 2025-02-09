<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GalleryCommand extends Command
{
    private OutputInterface $output;
    private bool $rename = false;
    private bool $force = false;

    public function configure(): void
    {
        $this->setName('file:gallery');
        $this->setDescription('Organise files by their SHA references');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Directory containing files to organise');
        $this->addOption('do-rename', 'x', InputOption::VALUE_NONE, 'Rename the file (report only without)');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Rename the file (report only without)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = strval($input->getArgument('directory'));
        $this->rename = boolval($input->getOption('do-rename'));
        $this->force = boolval($input->getOption('force'));

        $this->output = $output;

        if (!is_dir($directory)) {
            $this->output->writeln(sprintf('<error>Not a directory: %s</error>', $directory));
            return self::FAILURE;
        }

        $this->traverse(realpath($directory));

        return self::SUCCESS;
    }

    public function traverse(string $directory): void
    {
        $this->output->writeln(sprintf('Traversing directory: %s', $directory));

        $dir = dir($directory);
        while (false !== ($entry = $dir->read())) {
            if (!in_array($entry, ['.', '..'])) {
                $this->action($dir->path . DIRECTORY_SEPARATOR . $entry);
            }
        }
    }

    protected function action(string $path): void
    {
        if (!is_dir($path)) {
            $this->nameFileBySha($path);
        }
    }

    protected function nameFileBySha(string $path): void
    {
        $fileinfo = pathinfo($path);
        $filesha = sha1_file($path);

        $subdir = substr($filesha, 0, 1);
        if ($this->rename && !is_dir($fileinfo['dirname'] . DIRECTORY_SEPARATOR . $subdir)) {
            mkdir($fileinfo['dirname'] . DIRECTORY_SEPARATOR . $subdir, 0755);
        }

        $target = sprintf("%s/%s/%s.%s", $fileinfo['dirname'], $subdir, $filesha, $fileinfo['extension']);

        $outputHighlightTarget = match (true) {
            $path === $target => sprintf('<comment>%s</comment>', $target),
            file_exists($target) => sprintf('<error>%s</error>', $target),
            default => sprintf('<info>%s</info>', $target)
        };

        $this->output->write(sprintf('Moving file: %s => %s', $path, $outputHighlightTarget));

        if ($path !== $target && (!file_exists($target) || $this->force)) {
            if ($this->rename) {
                rename($path, $target);
                $this->output->write('  ... renamed');
            } else {
                $this->output->write(' ... dry run');
            }
        }

        $this->output->writeln('');
    }
}
