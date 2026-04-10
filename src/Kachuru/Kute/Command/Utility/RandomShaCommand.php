<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Utility;

use App\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RandomShaCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('utility:random-sha');
        $this->setAliases(['randsha']);
        $this->setDescription('Generate a random SHA hash');
        $this->addOption('salt', 's', InputOption::VALUE_OPTIONAL, 'Additional salt for random SHA', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $salt = $input->getOption('salt');
        if (!is_scalar($salt)) {
            throw new \InvalidArgumentException('Option "salt" must be a scalar value');
        }

        $output->writeln(sprintf('<info>%s</info>', hash('SHA256', (microtime(true) * (mt_rand(0, mt_getrandmax()) / mt_rand(0, mt_getrandmax()-1)))  . $salt)));

        return self::SUCCESS;
    }
}
