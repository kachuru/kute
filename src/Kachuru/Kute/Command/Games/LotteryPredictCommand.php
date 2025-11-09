<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Games;

use App\Command\Command;
use Kachuru\Lottery\FetchResults;
use Kachuru\Lottery\ResultsFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LotteryPredictCommand extends Command
{
    public function __construct(
        private readonly ResultsFile $resultsFile,
        private readonly FetchResults $fetchResults,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('games:lottery:predict');
        $this->setDescription('Predict the lottery numbers');
        $this->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Use file as source instead of default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceFile = $input->getOption('source');
        if (!is_null($sourceFile)) {
            if (!is_string($sourceFile) || !file_exists($sourceFile)) {
                throw new \InvalidArgumentException('Invalid source file');
            }
        }

        $mostRecent = $this->resultsFile->getMostRecentResultDate();

        $output->writeln('Most recent draw: ' . $mostRecent->format('d/m/Y'));

        $newResults = $this->fetchResults->since($mostRecent);
        if (!empty($newResults)) {
            $this->resultsFile->addResults($newResults);
        }

        return self::SUCCESS;
    }
}
