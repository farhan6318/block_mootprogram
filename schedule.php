<?php

require('../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/schedule.php');
$PAGE->set_url($url);
$title = 'Program Schedule';
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();
//echo $OUTPUT->heading('Program Schedule');
//$DB->set_debug(true);
$dates = $DB->get_records_sql("SELECT DISTINCT TO_CHAR(to_timestamp(timestart), 'DDMMYYYY') as timestamps, TO_CHAR(to_timestamp(timestart), 'Day') as days from {block_mootprogram}
ORDER BY timestamps");
if (is_siteadmin()) {
    $siteadmin = true;
} else {
    $siteadmin = null;
}
$rows = [];
foreach ($dates as $date) {
   // $DB->set_debug(true);
    $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
             FROM {block_mootprogram} p
         LEFT JOIN {user} u ON u.id = p.userid
            WHERE ".$DB->sql_like("TO_CHAR(to_timestamp(timestart), 'DDMMYYYY')", ":param")."
            ORDER BY timestart";

    $presentations = $DB->get_records_sql($sql, ['param' => $date->timestamps]);
    //die(print_object($presentations));
    $presentationsdata = [];
    foreach ($presentations as $presentation) {
        $presentation->issiteadmin = $siteadmin;
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
        if ($presentation->speakerlist) {
            $presenterlist = [];
            $speakers = explode(',', $presentation->speakerlist);
            foreach ($speakers as $speaker) {
                $speakeruserid = $DB->get_field_select('user', 'id', $DB->sql_like($DB->sql_fullname(), ':speaker'), ['speaker' => $speaker]);
                if ($speakeruserid) {
                    $presenterlist[] = "<a href='".$CFG->wwwroot."/user/profile.php?id=".$speakeruserid."'>".$speaker."</a>";
                } else {
                    $presenterlist[] = $speaker;
                }
            }
            $presenterlist = rtrim(implode(",", $presenterlist), ",");
        }
        $presentation->presenterlist = $presenterlist;
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

        if (!$imageurl)
        {
           $imageurl = 'https://picsum.photos/20'.rand(0,9);
        }
        $presentation->imageurl = $imageurl;
        $presentationsdata[] = $presentation;
    }
    if (count($rows) == 0) {
        $active = "active";
    } else {
        $active = null;
    }
    $rows[] = [
        'timestart' => trim($date->days),
        'presentation' => $presentationsdata,
        'active' => $active
    ];

}


$data = [
    'schedule' => $rows
];

echo $OUTPUT->render_from_template('block_mootprogram/schedule', $data);

echo $OUTPUT->footer();

