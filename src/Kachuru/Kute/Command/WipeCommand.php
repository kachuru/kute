<?php

namespace Kachuru\Kute\Command;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class WipeCommand extends Command
{
    public function configure()
    {
        $this->setName('wipe');
        $this->addArgument('filename', InputArgument::REQUIRED, 'File to wipe');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will wipe the contents of the file. Proceed? (y/N) ', false);

        if ($helper->ask($input, $output, $question)) {
            file_put_contents($input->getArgument('filename'), '');
        }

        return 0;
    }
}