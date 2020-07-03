<?php
require_once(__DIR__.'/../../config.php');

require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$events = $DB->get_records('event', ['uuid' => 'mootprogram']);

foreach ($events as $event) {
    $eventforupdate = calendar_event::load($event->id);

    $fromform = $DB->get_record('block_mootprogram', ['id' => $event->instance]);
    $courseid = course_id_mapper($fromform);
    $stream = "<a href='".$CFG->wwwroot."/course/view.php?id=".$courseid."'>".$fromform->room." Room</a>";
    $discussionlink = ($fromform->discussionlink) ? "<a href='".$fromform->discussionlink."'> Discuss here </a>" : null;

    $event->description = 'Presenter : ';
    if ($fromform->userid) {
        $user = $DB->get_record('user', ['id' => $fromform->userid]);
        $event->description .= "<a href='".$CFG->wwwroot."/user/profile.php?id=".$fromform->userid."'>".$user->firstname. " ".$user->lastname."</a>";
    }
    $event->description .= '<br/><br/>';
    if ($fromform->speakerlist) {
        $presenterlist = [];
        $speakers = explode(',', $fromform->speakerlist);
        foreach ($speakers as $speaker) {
            $speakeruserid = $DB->get_field_select('user', 'id', $DB->sql_like($DB->sql_fullname(), ':speaker'), ['speaker' => $speaker]);
            if ($speakeruserid) {
                $presenterlist[] = "<a href='".$CFG->wwwroot."/user/profile.php?id=".$speakeruserid."'>".$speaker."</a>";
            } else {
                $presenterlist[] = $speaker;
            }
        }
        $event->description .= rtrim(implode(",", $presenterlist), ",");
        $event->description .= '<br/><br/>';
    }
    $event->description .= 'Institute : '.$fromform->institute;
    $event->description .= '<br/><br/>';
    $event->description .=
        $fromform->description."<br/><br/> Stream: ".$stream;

    if ($discussionlink) {
        $event->description .= "<br/><br/>Discuss here: ". $discussionlink;
    }

    $event->desription = [
        'format' => FORMAT_HTML,
        'text' => $event->description
    ];
    $eventforupdate->update($event, false);
    echo "Updated event ".$event->name."<br/>";
}