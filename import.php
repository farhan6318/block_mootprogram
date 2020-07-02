<?php

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use core_competency\competency;
use core_competency\competency_framework;

require_once(__DIR__.'/../../config.php');

require_once("$CFG->libdir/formslib.php");
$pluginroot = $CFG->dirroot.'/blocks/mootprogram';

require_once("{$pluginroot}/vendor/autoload.php");

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/import.php');
$PAGE->set_url($url);
$title = 'Import Schedule';
$PAGE->set_heading($title);
$PAGE->set_title($title);

class import_competencies_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('filepicker', 'userfile', get_string('file'), null,['accepted_types' => '.xls,.xlsx']);
        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

echo $OUTPUT->header();

$mform = new import_competencies_form();

if ($fromform = $mform->get_data()) {
    $name = $mform->get_new_filename('userfile');

    $fullpath = $CFG->tempdir.'/'.$name;
    $success = $mform->save_file('userfile', $fullpath, true);


    $reader = new Xlsx();
    $spreadsheet = $reader->load($fullpath);
    $spreadsheet->setActiveSheetIndex(0);

    // get all excel data into an array
    $headers = [];
    $rows = [];
    $row_iterator = $spreadsheet->getActiveSheet()->getRowIterator();


    foreach ($row_iterator as $row_index => $row) {
        $cells = [];
        $cell_iterator = $row->getCellIterator();
        $cell_iterator->setIterateOnlyExistingCells(false);
        foreach ($cell_iterator as $cell_index => $cell) {
            $header = $headers[$cell_index] ?? $cell_index;
            $value = $cell->getFormattedValue();
            $cells[$header] = trim($value);
        }

        if ($row_index == 1) {
            $headers = $cells;
        } else {
            $rows[$row_index] = $cells;
        }
    }
    //die(print_object($rows));
    foreach ($rows as $row_index => $row) {
        $email = $row['Presenter email'];
        $userid = $DB->get_field('user', 'id', ['email' => $email, 'deleted' => 0], IGNORE_MULTIPLE);
        $record = (object) [
            'userid' => $userid ? $userid : null,
            'title' => $row['Title'],
            'description' => $row['description'],
            'timestart' => strtotime($row['UTC date time']),
            'length' => $row['duration'],
            'room' => $row['room'],
            'hightlight' => $row['highlight'],
            'sponsoredevent' => $row['sponsoredevent'],
            'institute' => $row['Institute']
        ];
        //print_object($record);
        $existingrecord = $DB->get_record_select('block_mootprogram', $DB->sql_like('title', ':title'), ['title' => $record->title]);
        //$DB->set_debug(true);
        if ($existingrecord) {
            $record->id = $existingrecord->id;
            $recordid = $record->id;
            $DB->update_record('block_mootprogram', $record);
        } else {
            $recordid = $DB->insert_record('block_mootprogram', $record);
        }

        $eventid = $DB->get_record('event', ['uuid' => 'mootprogram', 'instance' => $recordid]);
        if ($eventid) {
            $event = calendar_event::load($eventid);
        } else {
            $event = new stdClass();
        }
        if ($fromform->room == 'Education') {
            $courseid = 40;
        } else if ($fromform->room == 'Technology') {
            $courseid = 41;
        } else if ($fromform->room == 'Quiet') {
            $courseid = 50;
        } else if ($fromform->room == 'Chinese') {
            $courseid = 49;
        } else if ($fromform->room == 'Spanish') {
            $courseid = 51;
        } else if ($fromform->room == 'German') {
            $courseid = 52;
        }  else if ($fromform->room == 'French') {
            $courseid = 53;
        } else {
            $courseid = 40;
        }
        $event->eventtype = 'course'; // Constant defined somewhere in your code - this can be any string value you want. It is a way to identify the event.
        $event->type = CALENDAR_EVENT_TYPE_STANDARD; // This is used for events we only want to display on the calendar, and are not needed on the block_myoverview.
        $event->name = $fromform->title;
        $event->description = $fromform->description;
        $event->format = FORMAT_HTML;
        $event->courseid = $courseid;
        $event->uuid = 'mootprogram';
        $event->groupid = 0;
        $event->userid = 0;
        $event->instance = $recordid;
        $event->timestart = $fromform->timestart;
        $event->visible = 1;
        $event->timeduration = ($fromform->length) * 60;

        if ($eventid) {
            $event->id = $eventid;
            $event->update($event);
        } else {
            calendar_event::create($event);
        }
        print_object($record);
        //$DB->set_debug(false);
    }

    redirect($url, "Imported schedule successfully ", 5);

} else {
    $mform->display();
}


echo $OUTPUT->footer();