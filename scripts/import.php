<?php
include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\BostonRecords\Record\Citation;
use Balsama\BostonRecords\Importer\CsvImporter;
use Balsama\BostonRecords\Mapper;
use Balsama\BostonRecords\Storage;
use Balsama\BostonRecords\Record\Ticket;

$databaseFile = __DIR__ . '/../data/exports/' . 'attempt-2804.db';

//$importDirectory = __DIR__ . '/../data/citations/2021/';
$importDirectory = __DIR__ . '/../data/citations/2024/';
//$importDirectory = __DIR__ . '/../data/tickets/2023/';

$importer = new CsvImporter($importDirectory);
$storage = new Storage();
$storage->setDatabase($databaseFile);
$valueMap = Mapper::CITATION_2024;

$importer->entityType = Citation::class;
$tickets = $importer->extractRecords($valueMap, Citation::class);

echo number_format(count($tickets)) . ' tickets to process.' . PHP_EOL;
$importer->importRecords($storage, $tickets, $valueMap, 100);
