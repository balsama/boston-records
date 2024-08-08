# Boston Record ingest
Takes various records (parking tickets, traffic citations) from various sources (csvs from public records requests,
data.boston.gov) and standardizes them into a sqlite database.

## Database structure
See `Meedo::setDatabase` for database structure. Currently stores:
* Boston Parking Tickets (`tickets`)
* Boston Traffic Citations (`citations`)
* Log

## Known issues
* Citation data from 2021 (2011 - 2021) doesn't appear to include `year`, `make/model`, or `color` of vehicle.