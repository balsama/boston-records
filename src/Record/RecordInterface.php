<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Storage;
use PDOStatement;

interface RecordInterface
{
    public static function createFromArray(array $ticketValues, array $valueMap, string $className): Record;

    public function saveToDb(Storage $storage, array $valueMap): ?PDOStatement;

    public static function loadSavedRecordById(Storage $storage, string $id, array $valueMap): ?Record;

    public function loadInstanceSavedTicket(Storage $storage, array $valueMap): ?Record;

    public function calculateId(): string;
}
