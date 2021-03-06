<?php

require('../../config.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
$PAGE->requires->css($CFG->dirroot . '/blocks/mootprogram/styles.css');

$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/scheduleold.php');
$PAGE->set_url($url);
$title = get_string('programschedule', 'block_mootprogram');
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();
$dates = $DB->get_records_sql("SELECT DISTINCT TO_CHAR(to_timestamp(timestart), 'DDMMYYYY') as timestamps, TO_CHAR(to_timestamp(timestart), 'Day') as days from {block_mootprogram}
ORDER BY timestamps");
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
$rows = [];
$day = 1;
foreach ($dates as $date) {
    $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
             FROM {block_mootprogram} p
         LEFT JOIN {user} u ON u.id = p.userid
            WHERE ".$DB->sql_like("TO_CHAR(to_timestamp(timestart), 'DDMMYYYY')", ":param")."
            ORDER BY timestart";

    $presentations = $DB->get_records_sql($sql, ['param' => $date->timestamps]);
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

        $courseid = course_id_mapper($presentation);

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
        $forumid = forum_id_mapper($presentation);
        if ($forumid !== 0) {
            $durl = new moodle_url('/mod/forum/view.php', ['id' => $forumid]);
            $presentation->discussionlink = $presentation->discussionlink === '' ? $durl->out(false) : $presentation->discussionlink;
        }

        $presentationsdata[] = $presentation;
    }
    if (count($rows) == 0) {
        $active = "active";
    } else {
        $active = null;
    }

    $rows[] = [
        'timestart' => get_string('day', 'block_mootprogram', $day) . trim($date->days),
        'presentation' => $presentationsdata,
        'active' => $active,
        'classes' => 'four',
        'day' => $day,
    ];

    // Quick way to define what day of the Moot this relates to.
    $day++;
}

$data = [
    'schedule' => $rows
];

echo $OUTPUT->render_from_template('block_mootprogram/scheduleold', $data);

echo $OUTPUT->footer();

