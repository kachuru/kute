<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Time;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseSecondsCommand extends Command
{
    private const TIME_APERTURE = [
        's' => 60,
        'm' => 60,
        'h' => 25,
        'd' => -1
    ];

    public function configure()
    {
        $this->setName('time:parse-seconds');
        $this->setAliases(['ftime']);
        $this->setDescription('Parse number of seconds into friendlier format');
        $this->addArgument('seconds', InputArgument::REQUIRED, 'Seconds to parse');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $secs = $input->getArgument('seconds');

        $ftime = '';
        if (strpos($secs, '.')) {
            list($secs, $ms) = explode('.', $secs);
            $ftime = $ms . 'ms';
        }

        $times = [
            [60, 's'], [60, 'm'], [24, 'h'], [null, 'd']
        ];

        $remain = $secs;
        while ($remain > 0) {
            $time = array_shift($times);
            $ftime = $this->reduce($remain, $time[0], $time[1]) . ' ' . $ftime;
        }

        $output->writeln($ftime);

        return 0;
    }

    private function reduce(&$time, $denom, $symbol)
    {
        if (is_null($denom)) {
            $reduce = 0;
            $remain = $time;
        } else {
            $reduce = floor($time / $denom);
            $remain = $time % $denom;
        }

        $time = $reduce;
        return $remain . $symbol;
    }
}
