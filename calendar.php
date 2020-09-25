<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/calendar/lib.php');
echo "Testing creating a user calendar event";

$event = new stdClass();

$eventid = $DB->get_record('event', ['userid' => 2, 'instance' => 123]);
if ($eventid) {
    $eventforupdate = calendar_event::load($eventid->id);
}
$event->eventtype = 'user';
$event->type = CALENDAR_EVENT_TYPE_STANDARD;
$event->name = 'Test event';
$event->description = 'Description';
$event->userid = 2;
$event->format = FORMAT_HTML;
$event->courseid = 0;
$event->instance = 123;
$event->timestart = 1601012940 + (24*60*60);
$event->timeduration = (60) * 60;
if ($eventid) {
    $event->name = 'Updated';
    $eventforupdate->update($event, false);
} else {
    calendar_event::create($event);
}
//$event->delete()
echo "All done";