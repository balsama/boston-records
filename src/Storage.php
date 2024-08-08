<?php

namespace Balsama\BostonRecords;

use Medoo\Medoo;

class Storage
{
    public Medoo $database;

    public function setDatabase($sqliteDb = __DIR__ . '/../data/tickets.db'): Storage
    {
        $this->database = @new Medoo([
            'type' => 'sqlite',
            'database' => $sqliteDb,
        ]);

        $this->database->create('records', [
            'id' => ['TEXT'],
            'type' => ['TEXT'],
            'issue_date' => ['TEXT'],
        ]);

        $this->database->create('tickets', [
            'ticket_id' => ['TEXT'],
            'ticket_number' => ['TEXT'],
            'ticket_issue_date' => ['TEXT'],
            'ticket_issue_time' => ['TEXT'],
            'issuing_agency' => ['TEXT'],
            'violation_code' => ['TEXT'],
            'violation_desc_long' => ['TEXT'],
            'route' => ['TEXT'],
            'location' => ['TEXT'],
            'issuing_office_badge_number' => ['TEXT'],
            'vehicle_body_style' => ['TEXT'],
            'vehicle_color_desc' => ['TEXT'],
            'vehicle_make' => ['TEXT'],
            'plate_type' => ['TEXT'],
            'rp_plate_state' => ['TEXT'],
            'rp_plate' => ['TEXT'],
            'fine_amount' => ['FLOAT'],
            'street_no' => ['TEXT'],
            'street_direction' => ['TEXT'],
            'street_name' => ['TEXT'],
            'street_suffix' => ['TEXT'],
            'tick_disposition' => ['TEXT'],
        ]);

        $this->database->create('citations', [
            'ticket_id' => ['TEXT'],
            'issuing_agency' => ['TEXT'],
            'agency_code' => ['TEXT'],
            'officerid' => ['TEXT'],
            'event_date' => ['TEXT'],
            'viol_type' => ['TEXT'],
            'citation_number' => ['TEXT'],
            'citation_type' => ['TEXT'],
            'offense' => ['TEXT'],
            'offense_description' => ['TEXT'],
            'disposition' => ['TEXT'],
            'disposition_desc' => ['TEXT'],
            'location_name' => ['TEXT'],
            'searched' => ['TEXT'],
            'crash' => ['TEXT'],
            'court_code' => ['TEXT'],
            'race' => ['TEXT'],
            'gender' => ['TEXT'],
            'year_of_birth' => ['TEXT'],
            'lic_state' => ['TEXT'],
            'lic_class' => ['TEXT'],
            'cdl' => ['TEXT'],
            'platetype' => ['TEXT'],
            'vhc_state' => ['TEXT'],
            'vhc_year' => ['TEXT'],
            'make_model' => ['TEXT'],
            'commercial' => ['TEXT'],
            'vhc_color' => ['TEXT'],
            'sixteen_pass' => ['TEXT'],
            'hazmat' => ['TEXT'],
            'amount' => ['TEXT'],
            'hearing_requested' => ['TEXT'],
            'speed' => ['TEXT'],
            'posted_speed' => ['TEXT'],
            'viol_speed' => ['TEXT'],
            'posted' => ['TEXT'],
            'radar' => ['TEXT'],
            'clocked' => ['TEXT'],
        ]);

        $this->database->create('log', [
            'severity' => ['TEXT'],
            'message' => ['TEXT'],
        ]);

        return $this;
    }

    public function log(string $severity, string $message): void
    {
        $this->database->insert('log', ['severity' => $severity, 'message' => $message]);
    }
}
