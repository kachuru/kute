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
        $this->addOption('unix', 'u', InputOption::VALUE_NONE, 'Convert to Unix line-endings');
        $this->addOption('windows', 'w', InputOption::VALUE_NONE, 'Convert to Windows line-endings');
        $this->addOption('mac', 'm', InputOption::VALUE_NONE, 'Convert to Mac line-endings');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('auto_detect_line_endings', "1");

        $filename = $input->getArgument('filename');

        $unix = $input->getOption('unix');
        $windows = $input->getOption('windows');
        $mac = $input->getOption('mac');

        if ((int) $unix + (int) $windows + (int) $mac == 0) {
            $unix = true;
        }

        if ((int) $unix + (int) $windows + (int) $mac > 1) {
            throw new \InvalidArgumentException('Can only specify one of unix, windows or mac type line-endings');
        }

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('"%s" file does not exist', $filename));
        }

        $le = self::LINE_ENDINGS['unix'];

        if ($windows) {
            $le = self::LINE_ENDINGS['windows'];
        }

        if ($mac) {
            $le = self::LINE_ENDINGS['mac'];
        }

        $fh = fopen($filename, 'r');

        while (true !== feof($fh)) {
            $line = rtrim(fgets($fh));
            $output->write($line . $le);
        }

        fclose($fh);

        return self::SUCCESS;
    }
}
