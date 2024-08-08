<?php
include_once __DIR__ . '/../vendor/autoload.php';
$inputFile = __DIR__ . '/../data/citations/2021/2011-2021.tsv';
$outputFile = __DIR__ . '/../data/citations/2021/2011-2021--' . time() . '.csv';
\Balsama\BostonParkingTickets\Converter::tsvToCsv($inputFile, $outputFile);