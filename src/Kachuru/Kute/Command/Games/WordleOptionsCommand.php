<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Games;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WordleOptionsCommand extends Command
{
    private const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private array $availableLetters = [];

    private array $words = [];

    public function configure()
    {
        $this->setName('games:wordle:options');

        $this->addArgument(
            'pattern',
            InputArgument::REQUIRED,
            'Pattern based on known letters (e.g. P_A__,_PA__,__A_P)'
        );

        $this->addOption(
            'used-letters',
            'u',
            InputOption::VALUE_OPTIONAL,
            'Letters that have been used and eliminated'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->words = $this->getWords();

        $patterns = explode(',', $input->getArgument('pattern'));

        $usedLetters = $input->getOption('used-letters');
        $usedLetters = !empty($usedLetters)
            ? str_split(strtoupper($usedLetters))
            : [];

        $this->availableLetters = array_diff(str_split(self::LETTERS), $usedLetters);

        foreach ($patterns as $pattern) {
            $this->selectLettersToTry(str_replace('_', '%s', $pattern, $num), [], $num - 1);
        }

        return 0;
    }

    private function getWords(): array
    {
        return array_filter(
            array_map(
                function ($word) {
                    return strtoupper(trim($word));
                },
                file('/usr/share/dict/words')
            ),
            function (string $word): bool {
                return strlen($word) == 5;
            }
        );
    }

    private function selectLettersToTry($pattern, $tryLetters, $num): void
    {
        foreach ($this->availableLetters as $letter) {
            $submitLetters = array_merge($tryLetters, [$letter]);

            if ($num > 0) {
                $this->selectLettersToTry($pattern, $submitLetters, $num - 1);
            } else {
                $this->validateCombination($pattern, $submitLetters);
            }
        }
    }

    private function validateCombination(string $pattern, array $tryLetters): void
    {
        $combination = vsprintf($pattern, $tryLetters);

        if (in_array($combination, $this->words)) {
            echo $combination . PHP_EOL;
        }
    }
}