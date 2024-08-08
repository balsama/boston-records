<?php

namespace Balsama\BostonRecords\Record;

use Balsama\BostonRecords\Exception\RecordAlreadyExistsException;
use Balsama\BostonRecords\Storage;
use Exception;
use PDOStatement;

class Ticket extends Record implements RecordInterface
{
    public ?Record $savedTicket;

    public $ticket_id;
    public string $recordType = 'ticket';

    // @todo Pass a new citation or ticket a Mapper which can sort the array in different ways.
    public function __construct(
        public string $ticket_number,
        public string $ticket_issue_date,
        public string $ticket_issue_time,
        public string $issuing_agency,
        public string $violation_code,
        public string $violation_desc_long,
        public string $route,
        public string $location,
        public string $issuing_office_badge_number,
        public string $vehicle_body_style,
        public string $vehicle_color_desc,
        public string $vehicle_make,
        public string $plate_type,
        public string $rp_plate_state,
        public string $rp_plate,
        public string | float $fine_amount,
        public string $street_no,
        public string $street_direction,
        public string $street_name,
        public string $street_suffix,
        public string $tick_disposition,
    ) {
        // Format Date
        $providedDate = $this->ticket_issue_date;
        try {
            $datetime = new \DateTime($providedDate);
        } catch (Exception $e) {
            throw new Exception("The provided $providedDate string is not in a readable format. The array order is probably out of whack. ", $e->getCode(), $e);
        }
        $this->ticket_issue_date = $datetime->format("Y-m-d");

        // Format time
        if (preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $ticket_issue_time)) {
            $this->ticket_issue_time = $ticket_issue_time . ':00';
        }

        // Format amount
        $this->fine_amount = (float) str_replace('$', '', $fine_amount);

        // Set ID based on values which must be unique across tickets
        $this->ticket_id = $this->calculateId();
    }


    public function saveToDb(Storage $storage, array $valueMap): ?PDOStatement
    {
        $this->loadInstanceSavedTicket($storage, $valueMap);
        if ($this->savedTicket) {
            $message = "TicketAlreadyExistsException: Ticket with ID $this->ticket_id already exists in the database.";
            throw new RecordAlreadyExistsException($message);
        }

        return $storage->database->insert(
            'tickets',
            [
                'ticket_id' => $this->ticket_id,
                'ticket_number' => $this->ticket_number,
                'ticket_issue_date' => $this->ticket_issue_date,
                'ticket_issue_time' => $this->ticket_issue_time,
                'issuing_agency' => $this->issuing_agency,
                'violation_code' => $this->violation_code,
                'violation_desc_long' => $this->violation_desc_long,
                'route' => $this->route,
                'location' => $this->location,
                'issuing_office_badge_number' => $this->issuing_office_badge_number,
                'vehicle_body_style' => $this->vehicle_body_style,
                'vehicle_color_desc' => $this->vehicle_color_desc,
                'vehicle_make' => $this->vehicle_make,
                'plate_type' => $this->plate_type,
                'rp_plate_state' => $this->rp_plate_state,
                'rp_plate' => $this->rp_plate,
                'fine_amount' => $this->fine_amount,
                'street_no' => $this->street_no,
                'street_direction' => $this->street_direction,
                'street_name' => $this->street_name,
                'street_suffix' => $this->street_suffix,
                'tick_disposition' => $this->tick_disposition,
            ]
        );
    }

    public function loadInstanceSavedTicket(Storage $storage, array $valueMap): ?Record
    {
        return $this->savedTicket = self::loadSavedRecordById($storage, $this->ticket_id, $valueMap);
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
            $this->ticket_number,
            $this->location,
            $this->violation_code,
            $this->violation_desc_long,
            $this->ticket_issue_date,
            $this->ticket_issue_time
        ];
        $id = md5(implode($parts));
        return $id;
    }

    public static function loadSavedRecordById(Storage $storage, string $id, array $valueMap): ?Record
    {
        $records = $storage->database->select(
            'tickets',
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
        $ticket = Ticket::createFromArray($ticketArray, $valueMap, Ticket::class);
        return $ticket;
    }
}
