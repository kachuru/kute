<?php
//$fh = fopen('var/tmp/numbers.csv', 'r');
//
//$headers = fgetcsv($fh);
//$draws = [];
//while (!feof($fh)) {
//    $row = fgetcsv($fh);
//
//    if (!empty($row)) {
//        $draws[] = array_combine($headers, $row);
//    }
//}
//
//fclose($fh);
//
//
//$frequency = [];
//for ($i = 1; $i <= 59; $i++) {
//    $frequency[sprintf('%02d', $i)] = 0;
//}
//
//foreach ($draws as $numbers) {
//    for ($i = 1; $i <= 6; $i++) {
//        $frequency[$numbers[sprintf('n%s', $i)]]++;
//    }
//}
//
//asort($frequency);
//
//$numbers = [];
//$lastTimes = null;
//foreach ($frequency as $number => $times) {
//
//    if (count($numbers) >= 6 && $times != $lastTimes) {
//        break;
//    }
//
//    $numbers[] = $number;
//    $lastTimes = $times;
//}
//
//shuffle($numbers);
//
//$numbers = array_slice($numbers, 0, 6);
//
//sort($numbers);
//
//
//
//echo "My lottery prediction for the next draw is: " . PHP_EOL;
//echo "\t" . implode(', ', $numbers) . PHP_EOL;
