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

$conferencerecord = $DB->get_record('block_mootprogram_conference', ['id' => $conferenceid]);
$confdatediff = ($conferencerecord->enddate - $conferencerecord->startdate);

$numdays = round($confdatediff / (60 * 60 * 24));
echo $OUTPUT->header();
//die("test");
$days = [];
/*
for ($daycount = 1; $daycount <= $numdays ; $daycount++) {
    $days[] = ['dayname' => 'Day '.$daycount];
}*/

$rows = [];


$records = $DB->get_records_sql("SELECT DISTINCT TO_CHAR(to_timestamp(starttime), 'DDMMYYYY')  AS sessiondate,
                                                      TO_CHAR(to_timestamp(starttime), 'Day') AS weekdayname
                                                FROM {block_mootprogram_timeslots}
                                               WHERE conferenceid = ?", [$conferenceid]);
$i = 1;
foreach ($records as $record) {
    $slots = $DB->get_records_sql("SELECT *
                                          FROM {block_mootprogram_timeslots}
                                         WHERE ".$DB->sql_like("TO_CHAR(to_timestamp(starttime), 'DDMMYYYY')", ":param1"),
            ['param1' => $record->sessiondate]);
    foreach ($slots as $slot) {
        $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
                  FROM {block_mootprogram} p
             LEFT JOIN {user} u ON u.id = p.userid
                  WHERE sessionslot = ?";

        $presentations = $DB->get_records_sql($sql, [$slot->id]);
        $presentationsdata = [];
        foreach ($presentations as $presentation) {
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

            $courseid = 1;

            try {
                $roomname = get_course($courseid)->fullname;
            } catch (dml_exception $e) {
                $roomname = get_string('session', 'block_mootprogram');
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
            $presentation->timeend = trim($presentation->timestart + ($presentation->length * 60));

            $url = new moodle_url('/course/view.php', ['id' => $courseid]);
            $presentation->sessionurl = $url->out(false);
            $eurl = new moodle_url('/blocks/mootprogram/edit.php', ['id' => $presentation->id]);
            $presentation->editUrl = $eurl->out(false);
            $uurl = new moodle_url('/user/profile.php', ['id' => $presentation->userid]);
            $presentation->userLink = $uurl->out(false);
            $forumid = 1;
            if ($forumid !== 0) {
                $durl = new moodle_url('/mod/forum/view.php', ['id' => $forumid]);
                $presentation->discussionlink = $presentation->discussionlink === '' ? $durl->out(false) : $presentation->discussionlink;
            }

            $presentationsdata[] = $presentation;
        }
        $rows[] = ['startime' => userdate($slot->starttime), 'presentations' => $presentations];
    }

    $days[] = ['dayname' => 'Day '.$i++, 'rows' => array_values($rows)];
}


$data = [
    'days' => $days
];

echo $OUTPUT->render_from_template('block_mootprogram/schedulenew', $data);

echo $OUTPUT->footer();

