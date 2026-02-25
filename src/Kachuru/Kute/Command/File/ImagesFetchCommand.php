<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImagesFetchCommand extends Command
{
    private Client $httpClient;

    public function __construct()
    {
        parent::__construct();

        $this->httpClient = new Client();
    }

    protected function configure(): void
    {
        $this->setName('file:images:fetch');
        $this->setDescription('Download image files from URLs listed in input file');
        $this->addArgument('filename', InputArgument::REQUIRED, 'List of images to fetch');
        $this->addArgument('download-dir', InputArgument::REQUIRED, 'Directory to download to');
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Check the image files can be downloaded but do not download'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        $downloadDir = $input->getArgument('download-dir');

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist');
        }

        if (!file_exists($downloadDir) || !is_writable($downloadDir)) {
            throw new \InvalidArgumentException('The download directory is not writable');
        }

        $urls = file($filename);

        $dryRun = $input->getOption('dry-run');

        foreach ($urls as $url) {
            $url = trim($url);

            if (empty($url)) {
                continue;
            }

            if ($dryRun) {
                try {
                    $this->httpClient->head($url);
                } catch (ClientException $exception) {
                    $output->writeln(sprintf('File %s failed: %s', $url, $exception->getMessage()));
                }
            } else {
                try {
                    $output->write(sprintf('Downloading %s...', $url));
                    $basename = $downloadDir . DIRECTORY_SEPARATOR . basename($url);
                    $fileContent = file_get_contents($url);
                    $output->write(sprintf(' Writing to %s', $basename));
                    file_put_contents($basename, $fileContent);
                    $output->writeln(' Done');
                } catch (\Exception $e) {
                    $output->write(' FAILED: ');
                    $output->writeln($e->getMessage());
                }
            }
        }

        return self::SUCCESS;
    }
}
