<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File\Media;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends Command
{
    public function configure(): void
    {
        $this
            ->setName('file:media:convert')
            ->setDescription('Convert media files in directory to MP4 (assuming codec installed)')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory to scan');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $directoryName = $input->getArgument('directory');
        if (!is_dir($directoryName)) {
            die("Provide directory to scan\n");
        }

        $directory = Dir($directoryName);
        $filesToProcess = [];

        while ($file = $directory->read()) {
            if (!is_dir($file)) {
                $finfo = pathinfo($file);
                if ($finfo['extension'] != 'mp4') {
                    $oldFile = $directoryName . $file;
                    $newFile = $directoryName . $finfo['filename'] . '.mp4';

                    if (!file_exists($newFile)) {
                        $filesToProcess[$oldFile] = $newFile;
                    } else {
                        die(sprintf('%s already exists in the destination' . PHP_EOL, $newFile));
                    }
                }
            }
        }

        $totalFiles = count($filesToProcess);
        printf('Files to process: %d' . PHP_EOL, count($filesToProcess));
        sleep(5);

        $filesProcessed = 0;

        foreach ($filesToProcess as $oldFile => $newFile) {
            $timeStart = microtime(true);

            shell_exec(sprintf("ffmpeg -i '%s' '%s'" . PHP_EOL, $oldFile, $newFile));
            unlink($oldFile);

            $timeEnd = microtime(true);

            $filesProcessed++;

            printf('Last file took: %ss' . PHP_EOL, number_format($timeEnd - $timeStart, 2));
            printf('Files Processed: %d/%d' . PHP_EOL, $filesProcessed, $totalFiles);
            sleep(3);
        }

        return self::SUCCESS;
    }
}
