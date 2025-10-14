<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File\Csv;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentCommand extends Command
{
    public function configure()
    {
        $this->setName('file:csv:segment');
        $this->addArgument('headers', InputArgument::REQUIRED, 'Comma-separated list of headers to return in the segment');
        $this->addArgument('file', InputArgument::IS_ARRAY, 'File to segment. If you provide a list of files it will concatenate all segments together');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $headers = $input->getArgument('headers');
        $files = $input->getArgument('file');

        print_r(explode(',', $headers));

        print_r($files);
    }
}