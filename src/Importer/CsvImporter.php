<?php

namespace Balsama\BostonRecords\Importer;

class CsvImporter extends ImporterBase implements ImporterInterface
{
    public function extractRecords(array $valueMap, string $entityType, ?int $offset = null): array
    {
        $tickets = [];
        foreach ($this->filesToImport as $file) {
            $tickets = array_merge(
                $tickets,
                $this->loadCsvFile(
                    $this->importDirectory . $file,
                    $valueMap,
                    $entityType
                )
            );
        }
        if ($offset) {
            $tickets = array_slice($tickets, $offset);
        }
        return $tickets;
    }
}
