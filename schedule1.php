<?php

require('../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/schedule.php');
$PAGE->set_url($url);
$title = 'Schedule';
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading('Program Schedule');

$dates = $DB->get_records_select('block_mootprogram', '', [], 'timestart', 'DISTINCT timestart');
$rows = [];
foreach ($dates as $date) {
    $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
             FROM {block_mootprogram} p
         LEFT JOIN {user} u ON u.id = p.userid
            WHERE timestart = ?";

    $presentations = $DB->get_records_sql($sql, [$date->timestart]);
    $presentationsdata = [];
    foreach ($presentations as $presentation) {
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
        if ($presentation->userid) {

            $user = $DB->get_record('user', ['id' => $presentation->userid]);
            $userpictureurl = '';
            if ($user) {
                $userpic = new \user_picture($user);
                // If the user has uploaded a profile picture, use it.
                /*if (!empty($userpic->user->picture)) {
                    $userpic->link = false;
                    $userpic->alttext = false;
                    $user->size = 128;
                    $userpic->visibletoscreenreaders = false;
                    $userpic->class = "profile";
                    $userpicture = $OUTPUT->render($userpic);
                }*/
                $userpic->size = true;
                $userpictureurl = $userpic->get_url($PAGE)->out();
                //$presentation->presentername = $user->firstname . ' ' . $user->lastname;
                $presentation->userpictureurl = $userpictureurl;
                $presentation->profiledescription = $user->description;
            }
        }

        $presentation->imageurl = $imageurl;
        $presentationsdata[] = $presentation;
    }
    $rows[] = [
        'timestart' => $date->timestart,
        'presentation' => $presentationsdata
    ];

}


$data = [
    'schedule' => $rows
];

echo $OUTPUT->render_from_template('block_mootprogram/schedule', $data);

echo $OUTPUT->footer();


