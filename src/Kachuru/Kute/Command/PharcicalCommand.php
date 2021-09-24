<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PharcicalCommand extends Command
{
    private const PHAR_FILENAME = 'kute.phar';
    private const KUTE_ENTRYPOINT = 'bin/kute';

    public function configure()
    {
        $this->setName('pharcical');
        $this->setDescription('Create a phar file based on this project');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->cleanupFiles();

            $phar = new \Phar(self::PHAR_FILENAME);
            $phar->startBuffering();
            $phar->setStub($this->generateStub($phar));
            $phar->buildFromDirectory('.');
            $phar->stopBuffering();
            $phar->compressFiles(\Phar::GZ);
            chmod(self::PHAR_FILENAME, 0770);

            $output->writeln(sprintf('%s succssfully created', self::PHAR_FILENAME));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function cleanupFiles(): void
    {
        if (file_exists(self::PHAR_FILENAME)) {
            unlink(self::PHAR_FILENAME);
        }

        if (file_exists(self::PHAR_FILENAME . '.gz')) {
            unlink(self::PHAR_FILENAME . '.gz');
        }
    }

    private function generateStub(\Phar $phar): string
    {
        $entryPointFileContents = file(self::KUTE_ENTRYPOINT);
        array_shift($entryPointFileContents);
        $phar['app.php'] = implode(PHP_EOL, $entryPointFileContents);
        return "#!/usr/bin/env php" . PHP_EOL . $phar->createDefaultStub('app.php');
    }
}