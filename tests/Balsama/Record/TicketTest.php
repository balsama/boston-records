<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Exception\RecordAlreadyExistsException;
use Balsama\BostonRecords\Mapper;
use Balsama\BostonRecords\Storage;

class TicketTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateFromCsvLine(): void
    {
        $lines = [
            '489295284,10/16/2020,11:00,OUTSIDE,330,NO PARKING,SH,MT VERNON ST / HANCOCK ST,000932,4D,BLUE,LNDR, ,,,$90,,X,MT,ST,',
            '488895886,01/01/2022,09:30,BOSTON POLICE,77,DRIVEWAY,421,31 CALDER ST,005620,4D,BLACK,FORD, ,CA,8PKD398,$25,31,X,CALDER,ST,',
            '784189372,01/03/2022,11:18,TRAFFIC AND PARKING,333,NO STOP / STAND,D04,CALUMET ST@HUNTINGTON AV,000209,4D,GREY,HOND,PA,MA,2LCH21,$90,,X,CALUMET ST@HUNTINGTON AV,,',
            '795191025,01/03/2023,08:48,TRAFFIC AND PARKING,51,W/IN 20 FT INTERSECT,SB,E 4TH ST@P ST,000228,4D,WHITE,BMW,PA,MA,1HED13,$40,,E,4TH,ST,,$53.00,',
        ];
        $valueMap = Mapper::TICKET_2023;
        foreach ($lines as $line) {
            $ticket = Ticket::createFromCsvLine($line, $valueMap, Ticket::class);
            $this->assertInstanceOf(Ticket::class, $ticket);
        }
    }

    public function testSaveToDb(): void
    {
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $storage = new Storage();
        $storage->setDatabase($dbfilename);
        $valueMap = Mapper::TICKET_2023;
        $ticket = Ticket::createFromCsvLine(
            '784189372,01/03/2022,11:18,TRAFFIC AND PARKING,333,NO STOP / STAND,D04,CALUMET ST@HUNTINGTON AV,000209,4D,GREY,HOND,PA,MA,2LCH21,$90,,X,CALUMET ST@HUNTINGTON AV,,',
            $valueMap,
            Ticket::class,
        );
        $pdoStatement = $ticket->saveToDb($storage, $valueMap);
        $this->assertInstanceOf("\PDOStatement", $pdoStatement);

        // Test trying to save existing ticket.
        $this->expectException(RecordAlreadyExistsException::class);
        $ticket->saveToDb($storage, $valueMap);
        unlink($dbfilename);
    }

    public function testLoadSavedTicket(): void
    {
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $storage = new Storage();
        $storage->setDatabase($dbfilename);
        $valueMap = Mapper::TICKET_2023;
        $ticket = Ticket::createFromCsvLine(
            '784189372,01/03/2022,11:18,TRAFFIC AND PARKING,333,NO STOP / STAND,D04,CALUMET ST@HUNTINGTON AV,000209,4D,GREY,HOND,PA,MA,2LCH21,$90,,X,CALUMET ST@HUNTINGTON AV,,',
            $valueMap,
            Ticket::class,
        );
        $ticket->saveToDb($storage, $valueMap);

        $ticket->loadInstanceSavedTicket($storage, $valueMap);
        $this->assertInstanceOf(Ticket::class, $ticket->savedTicket);
        unlink($dbfilename);
    }

    public function testLoadSavedTicketById(): void
    {
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $storage = new Storage();
        $storage->setDatabase($dbfilename);
        $valueMap = Mapper::TICKET_2023;
        $originalTicket = Ticket::createFromCsvLine(
            '784189372,01/03/2022,11:18,TRAFFIC AND PARKING,333,NO STOP / STAND,D04,CALUMET ST@HUNTINGTON AV,000209,4D,GREY,HOND,PA,MA,2LCH21,$90,,X,CALUMET ST@HUNTINGTON AV,,',
            $valueMap,
            Ticket::class,
        );
        $ticketId = $originalTicket->calculateId();
        $originalTicket->saveToDb($storage, $valueMap);
        $loadedTicket = Ticket::loadSavedRecordById($storage, $ticketId, $valueMap);

        $this->assertEquals($originalTicket->ticket_id, $loadedTicket->ticket_id);
        $this->expectException(RecordAlreadyExistsException::class);
        $loadedTicket->saveToDb($storage, $valueMap);
        unlink($dbfilename);
    }
}
