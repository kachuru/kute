<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertLineEndingsCommand extends Command
{
    private const LINE_ENDINGS = [
        'unix' => "\n",
        'windows' => "\r\n",
        'mac' => "\r"
    ];

    public function configure(): void
    {
        $this->setName('file:convert-line-endings');
        $this->setAliases(['fixle', 'file:fixle']);
        $this->addArgument('filename', InputArgument::REQUIRED, 'Filename to convert');
        $this->addOption(
            'type',
            't',
            InputOption::VALUE_OPTIONAL,
            'Line-endings type to convert to (windows/mac/unix)',
            'unix'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('auto_detect_line_endings', "1");

        $filename = realpath($input->getArgument('filename'));
        if (!$filename) {
            throw new \InvalidArgumentException(sprintf('"%s" file does not exist', $input->getArgument('filename')));
        }

        $type = $input->getOption('type');
        if (!array_key_exists($type, self::LINE_ENDINGS)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid line-endings type', $type));
        }

        $le = self::LINE_ENDINGS[$type];

        $fh = fopen($filename, 'r');

        while (false !== ($line = fgets($fh))) {
            $output->write(rtrim($line) . $le);
        }

        fclose($fh);

        return self::SUCCESS;
    }
}
