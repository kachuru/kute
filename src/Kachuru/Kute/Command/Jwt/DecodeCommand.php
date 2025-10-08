<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Jwt;

use App\Command\Command;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DecodeCommand extends Command
{
    public function configure(): void
    {
        $this->setName('jwt:decode');
        $this->setDescription('Decode a JWT token');
        $this->addArgument('jwt', InputArgument::REQUIRED);
        $this->addOption('signature', 's', InputOption::VALUE_REQUIRED);
        $this->addOption('algorithm', 'a', InputOption::VALUE_OPTIONAL, 'One of HS256, HS384, HS512', 'HS512');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = new Key(
            $input->getOption('signature'),
            $input->getOption('algorithm')
        );

        $output->writeln(print_r(JWT::decode($input->getArgument('jwt'), $key), true));

        return self::SUCCESS;
    }
}
