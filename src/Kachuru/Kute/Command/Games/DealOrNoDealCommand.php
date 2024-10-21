<?php

namespace Kachuru\Kute\Command\Games;

use App\Command\Command;
use Kachuru\DealOrNoDeal\Game;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DealOrNoDealCommand extends Command
{
    public function __construct(private readonly Game $game) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('games:deal-or-no-deal');
        $this->setAliases(['games:dnod']);
        $this->addOption('games', 'g', InputOption::VALUE_REQUIRED, 'Number of games to play', 1);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $games = $input->getOption('games');

        $won = 0;
        $lost = 0;

        for ($gameNumber = 1; $gameNumber <= $games; $gameNumber++) {
            $game = $this->game->init();

            $playerBox = $game->draw();

            $output->writeln(sprintf('Player drew box <info>%d</info>', $playerBox->getNumber()));

            for ($turn = 1; $turn <= 20; $turn++) {
                $nextBox = $game->draw();
                $output->writeln(sprintf(
                    '  %02d: Player selected box <comment>%d</comment>. It contained <info>%s</info>',
                    $turn,
                    $nextBox->getNumber(),
                    $this->getValueToShow($nextBox->getValue())
                ));

                if ($this->shouldBankerOffer($turn)) {
                    $prizes = $game->remainingPrizes();
                    sort($prizes);
                    $prizes = array_map(function (float $prize) {
                        return sprintf('<comment>%s</comment>', $this->getValueToShow($prize));
                    }, $prizes);
                    $output->writeln(sprintf('  Remaining prizes: %s', implode(', ', $prizes)));
                    $output->writeln(sprintf('Banker offer: <comment>£%d</comment>', $game->bankerOffer()));
                }
            }

            $finalBox = $game->draw();
            $output->writeln(sprintf(
                '  Final Box: <info>%d</info>. It contained <comment>%s</comment>',
                $finalBox->getNumber(),
                $this->getValueToShow($finalBox->getValue())
            ));
            $output->writeln(sprintf(
                '  Player\'s box held <comment>%s</comment>',
                $this->getValueToShow($playerBox->getValue())
            ));

            if ($playerBox->getValue() > $finalBox->getValue()) {
                $output->writeln('The player won!!!');
                $won++;
            } else {
                $output->writeln('The player lost');
                $lost++;
            }

            $output->writeln('');
        }

        if ($games > 1) {
            $output->writeln(sprintf(
                'Played <info>%d</info> games. Won %d. Lost %d. (%0.2f%%)',
                $games,
                $won,
                $lost,
                (100/$games)*$won
            ));
        }

        return self::SUCCESS;
    }

    private function getValueToShow($value): string
    {
        return $value >= 1
            ? sprintf('£%d', $value)
            : sprintf('%dp', $value*100);
    }

    private function shouldBankerOffer(int $turn): bool
    {
        if ($turn == 5) {
            return true;
        }

        if ($turn > 5 && ($turn - 5) % 3 == 0) {
            return true;
        }

        return false;
    }
}
