<?php

namespace Balsama\BostonRecords\Importer;

interface ImporterInterface
{
    public function extractRecords(array $valueMap, string $entityType, ?int $offset = null);
}
