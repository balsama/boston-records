<?php

namespace Balsama\BostonRecords\Record;

use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $ticketValues = ['foo', 'bar', 'baz'];
        $valueMap = [
            'foo_key' => 0,
            'bar_key' => 1,
            'baz_key' => 2,
        ];

        $record = Record::createFromArray(
            // Well, Record isn't setup to accept any params.
            // @todo make record its own table with record_id, etc.
            $ticketValues,
            $valueMap,
            Record::class,
        );
        $this->assertInstanceOf(Record::class, $record);
    }
}
