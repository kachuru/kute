<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Tools;

use App\Command\Command;
use Kachuru\Kute\Tools\VersionFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    public function __construct(
        private readonly VersionFile $versionFile
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('tools:version');
        $this->setAliases(['version']);
        $this->setDescription('Get the current version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->versionFile->currentVersion());
        return self::SUCCESS;
    }
}