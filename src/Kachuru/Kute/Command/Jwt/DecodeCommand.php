<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Jwt;

use App\Command\Command;
use Firebase\JWT\JWT;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DecodeCommand extends Command
{
    public function configure()
    {
        $this->setName('jwt:decode');

        $this->addArgument('jwt', InputArgument::REQUIRED);

        $this->addOption('signature', 's', InputOption::VALUE_REQUIRED);

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            print_r(
                JWT::decode(
                    $input->getArgument('jwt'),
                    $input->getOption('signature'),
                    ['HS256', 'HS384', 'HS512']
                ),
                true
            )
        );

        return 0;
    }
}
