<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use block_mootprogram\form\edit_session_form;
require('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();

$conferenceid = required_param('conferenceid', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_context($context);

$url = new moodle_url('/blocks/mootprogram/editsessions.php', ['conferenceid' => $conferenceid]);
$PAGE->set_url($url);
$title = 'Edit Conference';
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();

if ($delete != 0) {
    if (!$confirm) {
        echo $OUTPUT->confirm('Are you sure you want to delete the session slot',
            '?delete='.$delete.'&confirm=1', '?delete=0&confirm=0');
    } else {
        $DB->delete_records('block_mootprogram_timeslots', ['id' => $delete]);
        \core\notification::success(get_string('deleted'));
    }

}
if (!$edit) {
    $mform = new edit_session_form($url, ['conferenceid' => $conferenceid]);
} else {
    $mform = new edit_session_form($url);
    $mform->set_data($DB->get_record('block_mootprogram_timeslots', ['id' => $edit]));
}

if ($fromform = $mform->get_data()) {
    if ($fromform->id) {
        $DB->update_record('block_mootprogram_timeslots', $fromform);
        \core\notification::success('Session slot updated');
        $mform = new edit_session_form($url, ['conferenceid' => $conferenceid]);
    } else {
        $DB->insert_record('block_mootprogram_timeslots', $fromform);
        \core\notification::success('Session slot added');
    }
}

echo html_writer::tag('h2', html_writer::link(new moodle_url('/blocks/mootprogram/editconference.php'),
    'Back to edit conference page'));
$mform->display();

$records = $DB->get_records('block_mootprogram_timeslots', ['conferenceid' => $conferenceid], 'starttime');

$htmltable = new html_table();
$htmltable->head = ['id', 'start time', 'session length', 'manage'];
$rows = [];
foreach ($records as $record) {
    $row = new stdClass();
    $row->id = $record->id;
    $row->starttime = userdate($record->starttime);
    $row->sessionlength = $record->sessionlength;
    $row->manage = html_writer::link(new moodle_url($url->out(),
        ['conferenceid' => $conferenceid, 'edit' => $record->id]), get_string('edit'));
    $row->manage .= ' ' .html_writer::link(new moodle_url($url->out(),
        ['conferenceid' => $conferenceid, 'delete' => $record->id]), get_string('delete'));
    $rows[] = $row;
}
$htmltable->data = $rows;

echo html_writer::table($htmltable);

echo $OUTPUT->footer();
