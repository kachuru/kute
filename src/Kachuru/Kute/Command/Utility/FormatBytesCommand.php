<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Utility;

use App\Command\Command;
use Kachuru\Util\Math;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FormatBytesCommand extends Command
{
    public function __construct(
        private readonly Math $math
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('utility:format-bytes');
        $this->setAliases(['fbytes']);
        $this->setDescription('Format bytes into human-readable format');
        $this->addArgument('bytes', InputArgument::REQUIRED, 'Number of bytes to format');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->math->getBytes((int) $input->getArgument('bytes')));

        return self::SUCCESS;
    }
}
