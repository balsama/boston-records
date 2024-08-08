<?php

namespace Balsama\BostonRecords\Importer;

use Balsama\BostonRecords\Exception\RecordAlreadyExistsException;
use Balsama\BostonRecords\Record\Record;
use Balsama\BostonRecords\Storage;

class ImporterBase
{
    private int $processedRecords = 0;
    protected array $filesToImport;
    public ?string $entityType = null;

    public function __construct(
        protected string $importDirectory = __DIR__ . '/../../data/tickets/2023/',
    ) {
        $this->filesToImport = array_diff(scandir($importDirectory), ['..', '.', '.DS_Store']);
    }

    public function loadCsvFile(string $file, array $valueMap, string $entityType): array
    {
        $csv = file($file);
        $records = [];
        foreach ($csv as $line) {
            $record = $entityType::createFromCsvLine($line, $valueMap, $entityType);
            $records[] = $record;
        }
        return $records;
    }

    public function importRecords(
        Storage $storage,
        iterable $records,
        array $valueMap,
        ?int $limit = null,
    ): void {
        foreach ($records as $record) {
            $this->importRecord($record, $storage, $valueMap);
            $this->printProgress();
            if (is_int($limit)) {
                $limit--;
                if ($limit === 0) {
                    return;
                }
            }
        }
    }

    private function importRecord(Record $record, Storage $storage, array $valueMap): void
    {
        try {
            $record->saveToDb($storage, $valueMap);
        } catch (RecordAlreadyExistsException $e) {
            $storage->log('error', $e->getMessage());
        }
    }

    private function printProgress()
    {
        $this->processedRecords++;
        if ($this->processedRecords % 1000 == 0) {
            print date("G:i:s", time()) . " Imported $this->processedRecords records total.\n";
        }
    }
}
