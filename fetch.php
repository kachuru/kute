<?php

class FetchAllResults
{
    const YEAR_START = 2015;

    const DATE_START = '2015-10-10';

    const URL_BASE = 'http://lottery.merseyworld.com/cgi-bin/lottery';

    const URL_QUERY = 'days=2&Machine=Z&Ballset=0&order=1&show=1&year=%d&display=CSV';

    public function run()
    {
        $results = [];
        for ($year = self::YEAR_START; $year <= (int) date('Y'); $year++) {
            $results = array_merge($results, $this->fetchYearResults($year));
        }

        print_r($results);
    }

    private function fetchYearResults(int $year)
    {
        $resultsFile = sprintf('var/cache/results_%d.txt', $year);

        $fetchUrl = sprintf('%s?%s', self::URL_BASE, sprintf(self::URL_QUERY, $year));
        if (!file_exists($resultsFile)) {
            file_put_contents($resultsFile, file_get_contents($fetchUrl));
        }

        $file = fopen($resultsFile, 'r');
        $headers = array_map('trim', fgetcsv($file));

        $results = [];

        while (!feof($file)) {
            $fetchLine = fgetcsv($file);

            if (!empty($fetchLine)) {
                $line = array_map('trim', $fetchLine);
                $results[] = array_combine($headers, $line);
            }
        }

        fclose($file);

        return $results;
    }
}

//$fetcher = new FetchAllResults();
//$fetcher->run();

// die('That is enough' . PHP_EOL);

//
//$URL = '?';
//
//
//$unexpiredBoth = 'http://lottery.merseyworld.com/cgi-bin/lottery?days=2&Machine=Z&Ballset=0&order=1&show=1&year=2018&display=CSV';
//
//$fh = fopen('numbers.txt', 'r+');
//
//$headers = fgetcsv($fh);
//$current = [];
//while (!feof($fh)) {
//    $row = fgetcsv($fh);
//
//    if (!empty($row)) {
//        $current[$row[0]] = array_combine($headers, $row);
//    }
//}
//
//fclose($fh);
//
//$fh = fopen($unexpiredBoth, 'r');
//
//$headers = [];
//$lotto = [];
//$fw = fopen('numbers.txt', 'w+');
//fputcsv($fw, [
//    'date', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'bn'
//]);
//while (!feof($fh)) {
//    $row = array_map(function($value) {
//        return trim($value);
//    }, fgetcsv($fh));
//
//    if (!empty($headers) && count($headers) == count($row)) {
//        $row = array_combine($headers, $row);
//
//        $drawTs = strtotime(sprintf('%s-%s-%02s 00:00:00', $row['YYYY'], $row['MMM'], $row['DD']));
//        if ($drawTs >= strtotime('2015-10-10')) {
//            fputcsv($fw, [
//                date('Y-m-d', $drawTs),
//                $row['N1'],
//                $row['N2'],
//                $row['N3'],
//                $row['N4'],
//                $row['N5'],
//                $row['N6'],
//                $row['BN']
//            ]);
//
//            if (array_key_exists(date('Y-m-d', $drawTs), $current)) {
//                unset($current[date('Y-m-d', $drawTs)]);
//            }
//        }
//    }
//
//    if (array_key_exists(0, $row) && $row[0] == 'No.') {
//        $headers = $row;
//    }
//}
//
//foreach ($current as $remain) {
//    fputcsv($fw, $remain);
//}
//
//fclose($fh);
//fclose($fw);

