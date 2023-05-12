<?php

namespace Kachuru\Kute\Command\Network;

use App\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloudflareCheckIpsCommand extends Command
{
    const CF_HOST = 'https://www.cloudflare.com/';

    const IPV4_LIST = 'ips-v4';

    const IPV6_LIST = 'ips-v6';

    const IP_LISTS = [self::IPV4_LIST, self::IPV6_LIST];

    const MAIL_TO = 'web@kachuru.uk';

    const MAIL_FROM = 'server@kachuru.uk';

    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;

        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('network:cloudflare-check-ips');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->checkLocalFiles()) {
            return $this->createLocalFiles();
        }

        if (!$this->checkRemoteList()) {
            $output->writeln("Cloudflare IPs are out-of-date");

            mail(
                self::MAIL_TO,
                'Cloudflare IP List Out-of-Date',
                'The cloudflare IP list needs updating',
                ['From' => self::MAIL_FROM]
            );
        }

        return self::SUCCESS;
    }

    private function checkLocalFiles()
    {
        return array_reduce(self::IP_LISTS, function ($exists, $ipList) {
            $exists |= file_exists($this->getLocalFileName($ipList));
            return $exists;
        });
    }

    private function createLocalFiles()
    {
        return array_reduce(self::IP_LISTS, function ($success, $ipList) {
            printf('Creating %s list file... ' . PHP_EOL, $ipList);

            $localFile = $this->getLocalFileName($ipList);

            touch($localFile);

            if (!file_exists($localFile)) {
                throw new \RuntimeException('Could not create local file');
            }

            $success |= file_put_contents($localFile, $this->getRemoteList($ipList));
            return $success;
        });
    }

    private function checkRemoteList()
    {
        foreach (self::IP_LISTS as $ipList) {
            $localList = $this->getLocalList($ipList);
            $remoteList = $this->getRemoteList($ipList);

            return $localList == $remoteList;
        }
    }

    private function getLocalList($ipList)
    {
        return file_get_contents($this->getLocalFileName($ipList));
    }

    private function getRemoteList($ipList)
    {
        return file_get_contents(self::CF_HOST . $ipList);
    }

    private function getLocalFileName($ipList)
    {
        return sprintf('%s/%s.txt', $this->cacheDir, $ipList);
    }
}
