<?php

declare(strict_types=1);

namespace Kachuru\Lottery;

use DateTimeImmutable;

class ResultsFile
{
    public const FIELD_DRAW_DATE = 'DrawDate';
    public const FIELD_BALL_MAIN1 = 'Ball 1';
    public const FIELD_BALL_MAIN2 = 'Ball 2';
    public const FIELD_BALL_MAIN3 = 'Ball 3';
    public const FIELD_BALL_MAIN4 = 'Ball 4';
    public const FIELD_BALL_MAIN5 = 'Ball 5';
    public const FIELD_BALL_MAIN6 = 'Ball 6';
    public const FIELD_BALL_BONUS = 'Bonus Ball';
    public const FIELD_DRAW_NUMBER = 'DrawNumber';

    /** @var Results[] $results */
    private array $results;

    public function __construct(
        private readonly string $resultFile
    ) {
    }

    public function getFileName(): string
    {
        return $this->resultFile;
    }

    public function getMostRecentResultDate(): ?\DateTimeInterface
    {
        if (!file_exists($this->resultFile)) {
            return null;
        }

        $results = $this->loadResults();

        $result = $results[0];

        return $result->drawDate();
    }

    private function loadResults(): array
    {
        if (!isset($this->results)) {
            $this->results = [];

            $fh = fopen($this->resultFile, 'r');

            $headers = fgetcsv($fh);

            while (false !== ($row = fgetcsv($fh))) {
                $data = array_combine($headers, $row);

                $datetime = new DateTimeImmutable($data[self::FIELD_DRAW_DATE]);

                $this->results[] = new Results(
                    $datetime,
                    [
                        intval($data[self::FIELD_BALL_MAIN1]),
                        intval($data[self::FIELD_BALL_MAIN2]),
                        intval($data[self::FIELD_BALL_MAIN3]),
                        intval($data[self::FIELD_BALL_MAIN4]),
                        intval($data[self::FIELD_BALL_MAIN5]),
                        intval($data[self::FIELD_BALL_MAIN6]),
                    ],
                    intval($data[self::FIELD_BALL_BONUS]),
                    intval($data[self::FIELD_DRAW_NUMBER])
                );
            }

            fclose($fh);
        }

        return $this->results;
    }

    public function addResults(array $newResults): void
    {
        foreach ($newResults as $result) {
            array_unshift($this->results, $result);
        }
    }
}
