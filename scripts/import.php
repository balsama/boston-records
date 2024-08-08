<?php
include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\BostonParkingTickets\Record\Citation;
use Balsama\BostonParkingTickets\Importer\CsvImporter;
use Balsama\BostonParkingTickets\Mapper;
use Balsama\BostonParkingTickets\Storage;
use Balsama\BostonParkingTickets\Record\Ticket;

$databaseFile = __DIR__ . '/../data/exports/' . 'attempt-2802.db';

//$importDirectory = __DIR__ . '/../data/citations/2021/';
//$importDirectory = __DIR__ . '/../data/citations/2024/';
$importDirectory = __DIR__ . '/../data/tickets/2023/';

$importer = new CsvImporter($importDirectory);
$storage = new Storage();
$storage->setDatabase($databaseFile);
$valueMap = Mapper::TICKET_2023;

$importer->entityType = Ticket::class;
$tickets = $importer->extractRecords($valueMap, Ticket::class);

echo number_format(count($tickets)) . ' tickets to process.' . PHP_EOL;
$importer->importRecords($storage, $tickets, $valueMap, 100);
