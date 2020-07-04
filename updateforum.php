<?php

require_once(__DIR__.'/../../config.php');

require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$presentations = $DB->get_records('block_mootprogram');

echo "Starting to update forum discussions<br/><br/>";
foreach ($presentations as $presentation) {
    $coursemoduleid = forum_id_mapper($presentation);
    if ($coursemoduleid != 0) {
        // This is one of the "6 core room presentation"
        if (!$presentation->discussionlink) {
            // Need to create the discussion thread in the forum.

            if ($presentation->userid) {
                $presenter = $DB->get_field('user', $DB->sql_fullname(), ['id' => $presentation->userid]);
            }

            if ($presentation->speakerlist) {
                $presenter = $presentation->speakerlist;
            }


            $record = new stdClass();
            $record->name = $record->subject = substr($presentation->title . ' - '. $presenter, 0, 254);
            $record->course = course_id_mapper($presentation);
            $record->message = $presentation->description;
            $record->messageformat = FORMAT_HTML;
            $record->forum = $DB->get_field('course_modules', 'instance', ['id' => $coursemoduleid]);
            $record->mailnow = 0;
            $record->messagetrust = "";
            try {
                $discussionid = forum_add_discussion($record);
                $presentation->discussionlink = $CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussionid;
                $DB->update_record('block_mootprogram', $presentation);
            } catch (Exception $e) {
                echo "Failed to create discussion for ". $presentation->name."<br/>";
                continue;
            }
            echo "Created forum thread for " .$presentation->name."<br/>";
        }
    }
}