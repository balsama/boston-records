# Boston Record ingest
Takes various records (parking tickets, traffic citations) from various sources (csvs from public records requests,
data.boston.gov) and standardizes them into a sqlite database.

## Database structure
See `Meedo::setDatabase` for database structure. Currently stores:
* Boston Parking Tickets (`tickets`)
* Boston Traffic Citations (`citations`)
* Log

## Usage
```php
// Create an Importer and pass the directory that holds the data. The data can be spread across multiple files in the
// directory.
$importer = new CsvImporter(__DIR__ . '/../data/citations/2024/');
// Tell the importer which type of Record to use (must extend Record). 
$importer->entityType = Citation::class;

// Create a Storage and set the SQLite database file name (the database can be empty or non-existent as long as the
// directory in which it will live is writable).
$storage = new Storage();
$storage->setDatabase(__DIR__ . '/../data/exports/' . 'test.db');

// Extract the tickets using the importer. The first param is an array that maps the columns in the data to the database
// columns.
$tickets = $importer->extractRecords(Mapper::CITATION_2024, Citation::class);

echo number_format(count($tickets)) . ' tickets to process.' . PHP_EOL;

// Import the extracted tickets.
$importer->importRecords($storage, $tickets, Mapper::CITATION_2024, 100);
```

## Known issues
* Citation data from 2021 (2011 - 2021) doesn't appear to include `year`, `make/model`, or `color` of vehicle.