<?php

namespace Balsama\Importer;

use Balsama\BostonRecords\Importer\CsvImporter;
use Balsama\BostonRecords\Mapper;
use Balsama\BostonRecords\Storage;
use Balsama\BostonRecords\Record\Ticket;
use PHPUnit\Framework\TestCase;
use SplFileObject;

class CsvImporterTest extends TestCase
{
    public function testLoadFile(): void
    {
        $importer = new CsvImporter(__DIR__ . '/../../fixtures/sample_csvs/tickets/');
        $file = __DIR__ . '/../../fixtures/sample_csvs/tickets/sampleCsv1';
        $valueMap = Mapper::TICKET_2023;
        $importer->entityType = Ticket::class;
        $tickets = $importer->loadCsvFile($file, $valueMap, $importer->entityType);
        foreach ($tickets as $ticket) {
            $this->assertInstanceOf(Ticket::class, $ticket);
            $this->assertIsFloat($ticket->fine_amount);
            $this->assertGreaterThanOrEqual(0, $ticket->fine_amount);
            $this->assertIsString($ticket->location);
        }
    }

    public function testImportTickets(): void
    {
        $importDirectory = __DIR__ . '/../../fixtures/sample_csvs/tickets/';
        $importer = new CsvImporter($importDirectory);
        $tickets = $importer->extractRecords(Mapper::TICKET_2023, Ticket::class, 5);
        foreach ($tickets as $ticket) {
            /* @var \Balsama\BostonParkingTickets\Ticket $ticket */
            $ticketIds[] = $ticket->ticket_id;
        }

        $storage = new Storage();
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $storage->setDatabase($dbfilename);

        $valueMap = Mapper::TICKET_2023;

        $importer->importRecords($storage, $tickets, $valueMap);

        foreach ($ticketIds as $ticketId) {
            $loadedTicket = Ticket::loadSavedRecordById($storage, $ticketId, $valueMap);
            $this->assertEquals($ticketId, $loadedTicket->ticket_id);
        }
    }

    public function testExtractTickets(): void
    {
        $importDirectory = __DIR__ . '/../../fixtures/sample_csvs/tickets/';

        $expectedCount = 0;
        foreach (array_diff(scandir($importDirectory), ['..', '.']) as $file) {
            $file = new SplFileObject($importDirectory . $file, 'r');
            $file->seek(PHP_INT_MAX);
            $expectedCount = ($expectedCount + ($file->key() + 1));
        }

        $importer = new CsvImporter($importDirectory);
        $valueMap = Mapper::TICKET_2023;
        $tickets = $importer->extractRecords($valueMap, Ticket::class);
        $this->assertIsArray($tickets);
        $this->assertCount($expectedCount, $tickets);
        $this->assertInstanceOf(Ticket::class, reset($tickets));
        $this->assertEquals('487997462', $tickets[0]->ticket_number);

        $tickets = $importer->extractRecords($valueMap, Ticket::class, 10);
        $this->assertCount($expectedCount - 10, $tickets);
        $this->assertEquals('489661104', $tickets[0]->ticket_number);

        $tickets = $importer->extractRecords($valueMap, Ticket::class, 107);
        $this->assertCount($expectedCount - 107, $tickets);
        $this->assertEquals('490398193', $tickets[0]->ticket_number);
    }
}
