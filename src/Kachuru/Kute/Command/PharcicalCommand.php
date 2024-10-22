<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PharcicalCommand extends Command
{
    private const PHAR_FILENAME = 'kute.phar';
    private const KUTE_ENTRYPOINT = 'bin/kute';
    private const VERSION_FILE = 'versions.json';

    public function configure(): void
    {
        $this->setName('pharcical');
        $this->setDescription('Create a phar file based on this project');
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Version of the tool. This should be the same as a release in Github'
        );
        $this->addOption('algorithm', 'a', InputOption::VALUE_OPTIONAL, 'Algorithm for file hash', 'SHA256');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $newVersion = $input->getArgument('version');

            if (ini_get('phar.readonly')) {
                throw new \RuntimeException(
                    sprintf(
                        'Cannot create phar file with phar.readonly set to true' . PHP_EOL
                        . 'To run this command please use:' . PHP_EOL . PHP_EOL
                        . '    php -d phar.readonly=false bin/kute pharcical ' . $newVersion . PHP_EOL . PHP_EOL
                    )
                );
            }

            $this->cleanupFiles();

            $phar = new \Phar(self::PHAR_FILENAME);
            $phar->startBuffering();
            $phar->setStub($this->generateStub($phar));
            $phar->buildFromDirectory('.', '#/(config|home|public|src|translations|vendor)/#');
            $phar->stopBuffering();
            $phar->compressFiles(\Phar::GZ);
            chmod(self::PHAR_FILENAME, 0770);

            $algo = $input->getOption('algorithm');
            $fileHash = hash_file($algo, self::PHAR_FILENAME);
            $output->writeln(sprintf('%s: %s', $fileHash, self::PHAR_FILENAME));

            if (file_exists(self::VERSION_FILE)) {
                $versions = json_decode(file_get_contents(self::VERSION_FILE), true);
            }
            $versions[$newVersion] = [
                'version' => $newVersion,
                $algo => $fileHash
            ];
            file_put_contents(self::VERSION_FILE, json_encode($versions, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return self::SUCCESS;
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
