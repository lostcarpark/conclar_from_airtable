<?php

require 'vendor/autoload.php';

use \TANIOS\Airtable\Airtable;

const API_KEY = 'patSndazX1Q8JEsey.1a27ccba1ae99002a2999016e37068b3dd64345a61a6deceb555b1d8f6975487';
const BASE_ID = 'appF5rlVZwEyEkb64';
const PROG_ITEMS = 'tblo9woBOKi8ycgrx';
const PEOPLE = 'tblWyzIJV2f2jAQch';

$conClar = new ConClar();
$conClar->airTableExtract();

class ConClar {

  protected AirTable $airTable;

  public function __construct() {
    $this->airTable = new Airtable([
      'api_key' => API_KEY,
      'base'    => BASE_ID,
    ]);
  }

  function airTableExtract(): void {
    $this->programExtract();
    $this->peopleExtract();
  }

  protected function programExtract() {
    $progRequest = $this->airTable->getContent(PROG_ITEMS);
    $progResponse = $progRequest->getResponse();
    $items = $progResponse['records'];
    $program = [];
    foreach ($items as $item) {
      // Build tags array.
      $tags = [];
      $type = $item->fields->Type ?? FALSE;
      if ($type) {
        $tags[] = [
          'value' => $type,
          'category' => 'Type',
          'label' => $type,
        ];
      }
      // Get start and duration.
      $start = $item->fields->{'Start Time (from Schedule Slots) (from Schedule)'}[0] ?? FALSE;
      $end = $item->fields->{'End Time (from Schedule Slot) (from Schedule)'}[0] ?? FALSE;
      if ($start /*&& $end*/) {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        $mins = ($endDate->getTimestamp() - $startDate->getTimestamp()) / 60;
        // Build people array.
        $people = [];
        $moderator = $item->fields->{'Run By (People)'}[0] ?? FALSE;
        if (isset($moderator)) {
          $people[] = ['id' => $moderator, 'role' => 'moderator'];
        }
        $others = $item->fields->{'Assisting/Panelist (People)'} ?? [];
        foreach ($others as $other) {
          $people[] = ['id' => $other];
        }
        $program[] = [
          'id' => $item->id,
          'title' => $item->fields->Title,
          'tags' => $tags,
          'datetime' => $start,
          'mins' => $mins,
          'loc' => $item->fields->{'Room (from Schedule Slots) (from Schedule)'},
          'people' => $people,
          'desc' => $item->fields->Description ?? '',
          'links' => [],
        ];
      }
    }
    file_put_contents('programme.json', json_encode($program));
  }

  protected function peopleExtract() {
    $progRequest = $this->airTable->getContent(PEOPLE);
    $progResponse = $progRequest->getResponse();
    $items = $progResponse['records'];
    $people = [];
    foreach ($items as $item) {
      $name = $item->fields->Name ?? '';
      $lead = $item->fields->{'Programme Items Lead'} ?? [];
      $assist = $item->fields->{'Programme Items Assist/Panel'} ?? [];
      $prog = array_unique(array_merge($lead, $assist));
      if ($name && $prog) {
        $people[] = [
          'id' => $item->id,
          'name' => [ $name ],
          'sortname' => $name,
          'tags' => [],
          'prog' => $prog,
          'links' => [],
          'bio' => '',
        ];
      }
    }
    file_put_contents('people.json', json_encode($people));
  }
}
