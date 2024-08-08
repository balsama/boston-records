<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Exception\RecordAlreadyExistsException;
use Balsama\BostonRecords\Mapper;
use Balsama\BostonRecords\Storage;
use DateTime;
use Exception;
use PDOStatement;

class Citation extends Record implements RecordInterface
{
    public ?Record $savedTicket;

    public $ticket_id;
    public $event_date;
    public $event_time;
    public string $recordType = 'citation';

    public function __construct(
        public string $issuing_agency,
        public string $agency_code,
        public string $officerid,
        private string $event_date_raw,
        public string $viol_type,
        public string $citation_number,
        public string $citation_type,
        public string $offense,
        public string $offense_description,
        public string $disposition,
        public string $disposition_desc,
        public string $location_name,
        public string $searched,
        public string $crash,
        public string $court_code,
        public string $race,
        public string $gender,
        public string $year_of_birth,
        public string $lic_state,
        public string $lic_class,
        public string $cdl,
        public string $platetype,
        public string $vhc_state,
        public string $vhc_year,
        public string $make_model,
        public string $commercial,
        public string $vhc_color,
        public string $sixteen_pass,
        public string $hazmat,
        public string $amount,
        public string $hearing_requested,
        public string $speed,
        public string $posted_speed,
        public string $viol_speed,
        public string $posted,
        public string $radar,
        public string $clocked,
        private ?string $time_hh_raw = null,
        private ?string $time_mm_raw = null,
        private ?string $am_pm_raw = null,
    ) {
        $this->setEventDate($event_date_raw);
        $this->event_time = "$time_hh_raw:$time_mm_raw$am_pm_raw";

        // Set ID based on values which must be unique across tickets
        $this->ticket_id = $this->ticketId = $this->calculateId();
    }

    public function getDate(): string
    {
        return $this->eventDate->format('Y-m-d');
    }

    public function setEventDate(string $eventDateString): void
    {
        try {
            $eventDateTime = new DateTime($eventDateString);
        } catch (Exception) {
            $eventDateTime = new DateTime('1970');
        }
        $this->event_date = $eventDateTime->format('Y-m-d');
    }

    public function saveToDb(Storage $storage, array $valueMap): ?PDOStatement
    {
        $this->loadInstanceSavedTicket($storage, $valueMap);
        if ($this->savedTicket) {
            $message = "TicketAlreadyExistsException: Ticket with ID $this->ticketId already exists in the database.";
            throw new RecordAlreadyExistsException($message);
        }

        return $storage->database->insert(
            'citations',
            [
                'ticket_id' => $this->ticket_id,
                'issuing_agency' => $this->issuing_agency,
                'agency_code' => $this->agency_code,
                'officerid' => $this->officerid,
                'event_date' => $this->event_date,
                'viol_type' => $this->viol_type,
                'citation_number' => $this->citation_number,
                'citation_type' => $this->citation_type,
                'offense' => $this->offense,
                'offense_description' => $this->offense_description,
                'disposition' => $this->disposition,
                'disposition_desc' => $this->disposition_desc,
                'location_name' => $this->location_name,
                'searched' => $this->searched,
                'crash' => $this->crash,
                'court_code' => $this->court_code,
                'race' => $this->race,
                'gender' => $this->gender,
                'year_of_birth' => $this->year_of_birth,
                'lic_state' => $this->lic_state,
                'lic_class' => $this->lic_class,
                'cdl' => $this->cdl,
                'platetype' => $this->platetype,
                'vhc_state' => $this->vhc_state,
                'vhc_year' => $this->vhc_year,
                'make_model' => $this->make_model,
                'commercial' => $this->commercial,
                'vhc_color' => $this->vhc_color,
                'sixteen_pass' => $this->sixteen_pass,
                'hazmat' => $this->hazmat,
                'amount' => $this->amount,
                'hearing_requested' => $this->hearing_requested,
                'speed' => $this->speed,
                'posted_speed' => $this->posted_speed,
                'viol_speed' => $this->viol_speed,
                'posted' => $this->posted,
                'radar' => $this->radar,
                'clocked' => $this->clocked,
            ]
        );
    }

    /**
     * A ticket is considered unique based on the values of the ticket #, location, code, description, and issue
     * date/time.
     *
     * @return string
     */
    public function calculateId(): string
    {
        $parts = [
            $this->citation_number,
            $this->event_date,
            $this->viol_type,
            $this->offense_description,
        ];
        $id = md5(implode($parts));
        return $id;
    }

    /**
     * @param string $tsvLine
     *   Expected order:
     *   * Ticket number
     *   * Ticket issue date
     *   * Issue time
     *   * Agency
     *   * violation code
     *   * violation desc long
     *   * route
     *   * locatioin
     *   * Badge #
     *   * body style
     *   * ...
     * @return Ticket
     * @throws Exception
     */
    public static function createFromTsvLine(array $tsvLine): Citation
    {

        if (count($tsvLine) < 6) {
            throw new Exception("Expected csv to have at least 21 columns.");
        }

        $citation = self::createFromArray($tsvLine);
        return $citation;
    }

    public static function loadSavedRecordById(Storage $storage, string $id, array $valueMap): ?Record
    {
        $records = $storage->database->select(
            'citations',
            '*',
            ['ticket_id[=]' => $id],
        );
        if (count($records) === 0) {
            return null;
        }
        if (count($records) > 1) {
            throw new Exception("Somehow two Records with the same ID got saved to the database.");
        }

        // Remove the Ticket ID from the start of the array since it's deterministic and calculated on creation.
        array_shift($records[0]);
        $ticketArray = array_values($records[0]);

        // this needs a special CITATION_FROM_DB mapper bc once they're in the DB they have a different structure 🙈.
        $ticketArray['time_hh_raw'] = '00';
        $ticketArray['time_mm_raw'] = '00';
        $ticketArray['am_pm_raw'] = 'pm';
        $ticketArray = array_values($ticketArray);
        $ticket = Citation::createFromArray(
            $ticketArray,
            Mapper::CITATION_FROM_DB,
            Citation::class
        );
        return $ticket;
    }

    public function loadInstanceSavedTicket(Storage $storage, array $valueMap): ?Record
    {
        return $this->savedTicket = self::loadSavedRecordById($storage, $this->ticket_id, $valueMap);
    }
}
