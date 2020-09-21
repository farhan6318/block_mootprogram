<?php

require('../../config.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
$PAGE->requires->css($CFG->dirroot . '/blocks/mootprogram/styles.css');

$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/schedule.php');
$PAGE->set_url($url);
$title = get_string('programschedule', 'block_mootprogram');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$conferenceid = 1;

$records = $DB->get_records_sql("SELECT DISTINCT FROM_UNIXTIME(starttime, '%D %M %Y') AS sessiondate,
                                             FROM_UNIXTIME(starttime, '%W') AS weekdayname
                                        FROM {block_mootprogram_timeslots} 
                                       WHERE conferenceid = ? ",
    [$conferenceid]);

echo $OUTPUT->header();

$days = [];

$daycount = 1;
foreach ($records as $record) {
    $days[] = ['dayname' => 'Day '.$daycount. ' : '.$record->weekdayname];
    $daycount++;
}
$rows = [];
foreach ($records as $record) {
    $slots = $DB->get_records_sql("SELECT id
                                          FROM {block_mootprogram_timeslots}
                                         WHERE ".$DB->sql_like("FROM_UNIXTIME(starttime, '%D %M %Y')", ":param1"),
                  ['param1' => $record->sessiondate]);
    foreach ($slots as $slot) {
        $rows[$slot->id] = [];
        $sessions = $DB->get_records_sql("SELECT * FROM {block_mootprogram} WHERE sessionslot = ?", [$slot->id]);
        foreach ($sessions as $session) {
            $rows[$slot->id][$session->id] = $session;
        }
    }
}

print_object($rows);

$data = [
    'days' => $days
];

echo $OUTPUT->render_from_template('block_mootprogram/schedule', $data);

echo $OUTPUT->footer();

