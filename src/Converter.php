<?php

namespace Balsama\BostonRecords;

class Converter
{
    public static function tsvToCsv(string $inputFile, string $outputFile): void
    {
        $handle = fopen($inputFile, "r");
        $lines = [];
        if (($handle = fopen($inputFile, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                $lines[] = $data;
            }
            fclose($handle);
        }
        $fp = fopen($outputFile, 'w');
        foreach ($lines as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);
    }
}
