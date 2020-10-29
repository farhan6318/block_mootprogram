<?php
use file_storage;
require('../../config.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
$PAGE->requires->css($CFG->dirroot . '/blocks/mootprogram/styles.css');
require_once("$CFG->libdir/filestorage/file_storage.php");
$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/scheduleold.php');
$PAGE->set_url($url);
$title = get_string('programschedule', 'block_mootprogram');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$conferenceid = 2;
$PAGE->requires->js_call_amd('block_mootprogram/program', 'init');
echo $OUTPUT->header();
$dates = $DB->get_records_sql("
 SELECT DISTINCT TO_CHAR(to_timestamp(timestart), 'DDMMYYYY') AS timestamps,
                 TO_CHAR(to_timestamp(timestart), 'Day') AS days,
                 TO_CHAR(to_timestamp(timestart), 'Month DD') AS daymonth
            FROM {block_mootprogram} 
           WHERE conferenceid = :conferenceid ORDER BY timestamps", ['conferenceid' => $conferenceid]);
if (is_siteadmin()) {
    $siteadmin = true;
} else {
    $siteadmin = null;
}
if (isloggedin()) {
    $loggedin = true;
} else {
    $loggedin = null;
}
$days = [];

$day = 1;
$nostar = false;
if ($DB->count_records('block_mootprogram_starred', ['userid' => $USER->id]) == 0) {
   $nostar = true;
}
$dates[] = 'starred';

foreach ($dates as $date) {
    $rows = [];
    $currentslotid = 0;
    $flag = 0;
    $countofsessioninslot = 0;
    if ($date != 'starred') {
        $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
             FROM {block_mootprogram} p
         LEFT JOIN {user} u ON u.id = p.userid
            WHERE ".$DB->sql_like("TO_CHAR(to_timestamp(timestart - (60*60)), 'DDMMYYYY')", ":param")."
            ORDER BY timestart";

        $presentations = $DB->get_records_sql($sql, ['param' => $date->timestamps]);
    } else {
        $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername, ss.id as staredid
             FROM {block_mootprogram} p
         LEFT JOIN {user} u ON u.id = p.userid
          JOIN {block_mootprogram_starred} ss ON ss.sessionid = p.id AND ss.userid = ?
            WHERE sessionslot IS NOT NULL
            ORDER BY timestart";

        $presentations = $DB->get_records_sql($sql, [$USER->id]);
    }

    $presentationsdata = [];
    //die(print_object($presentations));
    foreach ($presentations as $presentation) {

        if ($countofsessioninslot == 1) {
            $currentslotid = $presentation->sessionslot;
        }
        $presentation->issiteadmin = $siteadmin;
        $presentation->isloggedin = $loggedin;
        $imageurl = null;
        if ($imageid = $presentation->image) {
            $fs = new \file_storage();
            $file = $fs->get_file_by_id($imageid);
            if ($file) {
                $imageurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                )->out();
            }
        }
        $presenterlist = null;
        if ($presentation->speakerlist) {
            $presenterlist = get_presenter_list($presentation);
        }
        $presentation->presenterlist = $presenterlist;

        if (!$presentation->courseid) {
            $courseid = course_id_mapper($presentation);
        } else {
            $courseid = $presentation->courseid;
        }


        try {
            $roomname = get_course($courseid)->fullname;
        } catch (dml_exception $e) {
            $roomname = get_string('session', 'block_mootprogram');
        }

        $presentation->starurl = (new moodle_url('/blocks/mootprogram/starsession.php', ['session' => $presentation->id]))->out();

        $presentation->starpage = false;
        if ($date == 'starred') {
            $presentation->isStared = true;
            $presentation->starpage = true;
        } else if ($DB->record_exists('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $presentation->id])) {
            $presentation->isStared = true;
        }

        $presentation->roomName = $roomname;

        if ($presentation->userid) {

            $user = $DB->get_record('user', ['id' => $presentation->userid]);
            $userpictureurl = '';
            if ($user) {
                $userpic = new \user_picture($user);
                $userpic->size = true;
                $userpictureurl = $userpic->get_url($PAGE)->out();
                $presentation->presentername = $user->firstname . ' ' . $user->lastname;
                $presentation->userpictureurl = $userpictureurl;
                $presentation->profiledescription = $user->description;
            }
        }

        if (!$imageurl)
        {
            $imageurl = 'https://picsum.photos/20'.rand(0,9);
        }
        $presentation->imageurl = $imageurl;
        $presentation->hidetimerow = true;
        $presentation->timeend = trim($presentation->timestart + ($presentation->length * 60));

        $url = new moodle_url('/course/view.php', ['id' => $courseid]);
        if (isset($presentation->sessionlink) && $presentation->sessionlink != '') {
            $presentation->sessionurl = $presentation->sessionlink;
        } else {
            $presentation->sessionurl = $url->out(false);
        }
        $eurl = new moodle_url('/blocks/mootprogram/edit.php', ['id' => $presentation->id]);
        $presentation->editUrl = $eurl->out(false);
        $uurl = new moodle_url('/user/profile.php', ['id' => $presentation->userid]);
        $presentation->userLink = $uurl->out(false);
        $forumid = forum_id_mapper($presentation);
        if ($forumid !== 0) {
            $durl = new moodle_url('/mod/forum/view.php', ['id' => $forumid]);
            $presentation->discussionlink = $presentation->discussionlink === '' ? $durl->out(false) : $presentation->discussionlink;
        }

        $presentationsdata[] = $presentation;



        if ($date == 'starred') {
            $sql = "SELECT COUNT(ss.id) 
                      FROM {block_mootprogram_starred} ss 
                      JOIN {block_mootprogram} p ON p.id = ss.sessionid AND p.sessionslot = :sessionslot
                      WHERE ss.userid = :userid";
            $countofsessioninslot = $DB->count_records_sql($sql, ['sessionslot' => $presentation->sessionslot, 'userid' => $USER->id]);
        } else {
            $countofsessioninslot = $DB->count_records('block_mootprogram', ['sessionslot' => $presentation->sessionslot]);
        }
        if ($countofsessioninslot == count($presentationsdata)) {
            $currentslotid = $presentation->sessionslot;
            $slotrecord = $DB->get_record('block_mootprogram_timeslots', ['id' => $presentation->sessionslot]);
            $rows[] = ['presentation' => $presentationsdata, 'timestart' => $slotrecord->starttime, 'timeend' => trim($slotrecord->starttime + ($slotrecord->sessionlength * 60)) ];
            $presentationsdata = [];
        }
        $flag = 1;

    }

    if (!count($presentationsdata) == 0) {
        $slotrecord = $DB->get_record('block_mootprogram_timeslots', ['id' => $presentation->sessionslot]);
        if (isset($slotrecord)) {
            $rows[] = ['presentation' => $presentationsdata, 'timestart' => $slotrecord->starttime, 'timeend' => trim($slotrecord->starttime + ($slotrecord->sessionlength * 60)) ];
        }
    }
    if (count($days) == 0) {
        $active = "active";
    } else {
        $active = null;
    }



    $days[] = [
        'timestart' => ($date != 'starred') ? get_string('day', 'block_mootprogram', $day) . trim($date->days) .' ' .trim(str_replace(" 0", " ",$date->daymonth)) : 'Starred',
        'active' => $active,
        'classes' => 'four',
        'day' => $day,
        'starpage' => ($date == 'starred') ? true : false,
        'rows' => array_values($rows)
    ];

    // Quick way to define what day of the Moot this relates to.
    $day++;
}

$data = [
        'schedule' => $days,
        'nostar' => $nostar
];
//die(print_object($data));

echo $OUTPUT->render_from_template('block_mootprogram/schedule', $data);

echo $OUTPUT->footer();

