<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Network;

use App\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TunnelNannyCommand extends Command
{
    private const SSH_COMMAND = 'ssh -N -D 8889 frontdoor -f';

    public function configure()
    {
        $this->setName('network:tunnel:nanny');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $result = [];
            exec('ps aux|grep "ssh -N -D 8889"', $result);

            $inUse = array_filter(
                $result,
                function ($entry) use ($output) {
                    return strstr($entry, self::SSH_COMMAND);
                }
            );

            if ($inUse) {
                $this->output($output, 'Tunnel established');
            } else {
                $result = [];
                $this->output($output, 'Tunnel not running... attempting to re-establish...');
                exec(self::SSH_COMMAND, $result);
                if (!empty($result)) {
                    die(print_r($result, true));
                }
                $this->output($output, 'Connected');
                continue;
            }

            sleep(300);
        }

        return 0;
    }

    private function output(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('[%s] > %s', date('Y-m-d H:i:s'), $message));
    }
}
