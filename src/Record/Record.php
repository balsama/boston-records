<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Storage;
use Exception;
use PDOStatement;

class Record
{
    private const EXCEPTION_MESSAGE = 'This should be implented in a subclass.';
    public ?Record $savedTicket;
    public string $recordType;
    public ?string $ticketId = null;

    public function saveToDb(Storage $storage, array $recordValues): ?PDOStatement
    {
        if (count($recordValues) !== 3) {
            throw new Exception("Expected 3 values (id, type, & issue_date)");
        }
        return $storage->database->insert(
            'records',
            [
                'id' => $recordValues[0],
                'type' => $recordValues[1],
                'issue_date' => $recordValues[2],
            ]
        );
    }

    public function calculateId(): string
    {
        throw new Exception(self::EXCEPTION_MESSAGE);
    }

    public static function createFromCsvLine(string $csvLine, array $valueMap, string $className): Record
    {
        $rawTicket = str_getcsv(strtolower($csvLine));

        if (count($rawTicket) < count($valueMap)) {
            throw new Exception("Expected csv to have at least as many columns as the provided valueMap.");
        }

        $ticket = self::createFromArray($rawTicket, $valueMap, $className);
        return $ticket;
    }

    public static function createFromArray(array $ticketValues, array $valueMap, string $className): Record
    {
        if ($className === Record::class) {
            return new Record();
        }
        if (count($ticketValues) < count($valueMap)) {
            throw new Exception("Expected ticketValues length to be at least as long as valueMap.");
        }
        $orderedValues = [];
        foreach ($valueMap as $key => $index) {
            $orderedValues[$key] = $ticketValues[$index];
        }

        $record = new $className(...$orderedValues);

        return $record;
    }
}
