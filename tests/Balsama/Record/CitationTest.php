<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Exception\RecordAlreadyExistsException;
use Balsama\BostonRecords\Mapper;
use Balsama\BostonRecords\Storage;
use PHPUnit\Framework\TestCase;

class CitationTest extends TestCase
{
    private Storage $storage;
    private string $ticketId;

    public function setUp(): void
    {
        parent::setUp();
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $this->storage = new Storage();
        $this->storage->setDatabase(
            $dbfilename,
        );
        $csvLine = 'Boston Police District B-3,PD_BPC,126548,1/1/2021,2,20,PM,1/1/2021,OPERATOR,T2395496,WARN,9060,NUMBER PLATE VIOLATION * c90 §6,, ,12/31/9999,Dorchester,N,N,CT_007,HISP,MALE,1994,MA,D,No,PAN,MA,2019,MITS MIRAGE,NO,BLACK,NO,NO,0.0000,N,N,0,0,,,';
        $citation = Citation::createFromCsvLine($csvLine, Mapper::CITATION_2021, Citation::class);
        $citation->saveToDb($this->storage, Mapper::CITATION_2021);
        $this->ticketId = $citation->ticketId;
    }

    public function testCreateFromCsvLine(): void
    {
        $lines = [
            'Boston Police District B-3,PD_BPC,126548,1/1/2021,2,20,PM,1/1/2021,OPERATOR,T2395496,WARN,9060,NUMBER PLATE VIOLATION * c90 §6,, ,12/31/9999,Dorchester,N,N,CT_007,HISP,MALE,1994,MA,D,No,PAN,MA,2019,MITS MIRAGE,NO,BLACK,NO,NO,0.0000,N,N,0,0,,,',
            'Boston Police District E-18,PD_BPL,126548,1/1/2021,2,30,PM,1/1/2021,OPERATOR,T2395497,WARN,9018A2,SPEEDING IN VIOL SPECIAL REGULATION * c90 §18,, ,12/31/9999,Dorchester,N,N,CT_007,BLACK,MALE,1961,MA,D,No,PAN,MA,2016,FORD ESCAPE,NO,GRAY,NO,NO,0.0000,N,Y,25,50,YES,RADAR,EST',
            'Boston Police District B-2,PD_BPB,132363,1/1/2021,7,30,AM,1/1/2021,OPERATOR,T2535949,WARN,9060,NUMBER PLATE VIOLATION * c90 §6,, ,12/31/9999,Roxbury,N,N,CT_002,HISP,FEMALE,1996,MA,D,No,PAN,MA,2017,INFI Q50,NO,GRAY,NO,NO,0.0000,N,N,0,0,,,',
        ];
        foreach ($lines as $line) {
            $citation = Citation::createFromCsvLine($line, Mapper::CITATION_2021, Citation::class);
            $this->assertInstanceOf(Citation::class, $citation);
        }
    }

    public function testSaveToDb(): void
    {
        $dbfilename = sys_get_temp_dir() . '/testdb-' . microtime();
        $storage = new Storage();
        $storage->setDatabase($dbfilename);
        $csvLine = 'Boston Police District B-3,PD_BPC,126548,1/1/2021,2,20,PM,1/1/2021,OPERATOR,T2395496,WARN,9060,NUMBER PLATE VIOLATION * c90 §6,, ,12/31/9999,Dorchester,N,N,CT_007,HISP,MALE,1994,MA,D,No,PAN,MA,2019,MITS MIRAGE,NO,BLACK,NO,NO,0.0000,N,N,0,0,,,';
        $citation = Citation::createFromCsvLine($csvLine, Mapper::CITATION_2021, Citation::class);

        $valueMap = Mapper::CITATION_2021;

        $pdoStatement = $citation->saveToDb($storage, $valueMap);
        $this->assertInstanceOf("\PDOStatement", $pdoStatement);

        // Test trying to save existing ticket.
        $this->expectException(RecordAlreadyExistsException::class);
        $citation->saveToDb($storage, $valueMap);
        unlink($dbfilename);
    }

    public function testLoadSavedRecordById(): void
    {
        $storage = $this->storage;
        $citation = Citation::loadSavedRecordById($storage, $this->ticketId, Mapper::CITATION_2021);

        $this->assertEquals($this->ticketId, $citation->ticketId);
    }
}
