<?php

declare(strict_types=1);

namespace Kachuru\Lottery;

use DateTimeImmutable;
use Kachuru\File\Csv;

class FetchResults
{
    private const REMOTE_RESULTS_FILE = 'https://www.national-lottery.co.uk/results/lotto/draw-history/csv';
    public const FIELD_DRAW_DATE = 'DrawDate';
    public const FIELD_BALL_MAIN1 = 'Ball 1';
    public const FIELD_BALL_MAIN2 = 'Ball 2';
    public const FIELD_BALL_MAIN3 = 'Ball 3';
    public const FIELD_BALL_MAIN4 = 'Ball 4';
    public const FIELD_BALL_MAIN5 = 'Ball 5';
    public const FIELD_BALL_MAIN6 = 'Ball 6';
    public const FIELD_BALL_BONUS = 'Bonus Ball';
    public const FIELD_DRAW_NUMBER = 'DrawNumber';

    /** @return Results[] */
    public function since(\DateTimeInterface $date): array
    {
        $results = [];

        $remoteResults = explode("\n", file_get_contents(self::REMOTE_RESULTS_FILE));

        $headers = explode(',', array_shift($remoteResults));
        foreach ($remoteResults as $result) {
            $data = array_combine($headers, explode(',', $result));

            $datetime = new DateTimeImmutable($data[self::FIELD_DRAW_DATE]);

            if ($datetime->getTimestamp() > $date->getTimestamp()) {
                $results[] = new Results(
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
        }

        return $results;
    }
}
