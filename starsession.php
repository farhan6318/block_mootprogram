<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_login();
$sessionid = required_param('session', PARAM_INT);
$noredirect = optional_param('noredirect', 0, PARAM_INT);

if ($DB->record_exists('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $sessionid])) {
    $DB->delete_records('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $sessionid]);
    $eventid = $DB->get_record('event', ['userid' => $USER->id, 'instance' => $sessionid, 'uuid' => 'mootprogram']);
    if ($eventid) {
        $event = calendar_event::load($eventid->id);
        $event->delete();
    }
} else {
    $DB->insert_record('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $sessionid]);
    $eventid = $DB->get_record('event', ['userid' => $USER->id, 'instance' => $sessionid, 'uuid' => 'mootprogram']);
    $event = new stdClass();
    $event->eventtype = 'user';
    $event->type = CALENDAR_EVENT_TYPE_STANDARD;
    $event->uuid = 'mootprogram';
    $event->format = FORMAT_HTML;
    $event->courseid = 0;
    $event->instance = $sessionid;
    $event->userid = $USER->id;
    $presentation = $DB->get_record('block_mootprogram', ['id' => $sessionid]);
    $event->timestart = $presentation->timestart;
    $event->timeduration = $presentation->length * 60;
    $event->name = $presentation->title;
    $room = $DB->get_field('course', 'fullname', ['id' => $presentation->courseid]);
    $stream = "<a href='".$CFG->wwwroot."/course/view.php?id=".$presentation->courseid."'>".$room." </a>";
    $event->description = 'Presenter : ';
    if ($presentation->userid) {
        $user = $DB->get_record('user', ['id' => $presentation->userid]);
        $event->description .= "<a href='".$CFG->wwwroot."/user/profile.php?id=".$presentation->userid."'>".$user->firstname. " ".$user->lastname."</a>";
    }
    $event->description .= '<br/><br/>';
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
        $event->description .= rtrim(implode(",", $presenterlist), ",");
        $event->description .= '<br/><br/>';
    }
    $event->description .= 'Institute : '.$presentation->institute;
    $event->description .= '<br/><br/>';
    $event->description .=
            $presentation->description."<br/><br/> Stream: ".$stream;
    if ($eventid) {
        $eventforupdate = calendar_event::load($eventid->id);
        $eventforupdate->update($event, false);
    } else {
        calendar_event::create($event);
    }

}
if (!$noredirect) {
    redirect(new moodle_url('/blocks/mootprogram/schedule.php'));
} else {
    header('Content-Type: application/json');
    echo json_encode('true');
}
