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

/**
 * Translation manager
 *
 * @package    tool
 * @subpackage translationmanager
 * @copyright  2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_mootprogram\schedule_table;
use block_mootprogram\form\search_form;
use block_mootprogram\form\edit_form;

require('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
$id = optional_param('id', 0, PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/edit.php');
$PAGE->set_url($url);
$title = 'Edit Schedule';
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_mootprogram'));

$mform = new edit_form();

if ($fromform = $mform->get_data()) {
    //$fromform->description = $fromform->description['text'];
    if (!isset($fromform->length))  {
        $fromform->length = 60;
    }
    if ($fromform->room == 'Education') {
        $courseid = 40;
    } else if ($fromform->room == 'Technology') {
        $courseid = 41;
    } else if ($fromform->room == 'Quiet') {
        $courseid = 50;
    } else if ($fromform->room == 'Chinese') {
        $courseid = 49;
    } else if ($fromform->room == 'Spanish') {
        $courseid = 51;
    } else if ($fromform->room == 'German') {
        $courseid = 52;
    }  else if ($fromform->room == 'French') {
        $courseid = 53;
    }
    if ($fromform->id) {
        $recordid = $fromform->id;
        $DB->update_record('block_mootprogram', $fromform);
    } else {
        $recordid = $DB->insert_record('block_mootprogram', $fromform);
    }

    $eventid = $DB->get_record('event', ['uuid' => 'mootprogram', 'instance' => $recordid]);

    $event = new stdClass();

    if ($eventid) {
        $eventforupdate = calendar_event::load($eventid->id);
    }

    $stream = "<a href='".$CFG->wwwroot."/course/view.php?id=".$courseid."'>".$fromform->room." Room</a>";
    $discussionlink = "<a href='".$fromform->discussionlink."'> Discuss here </a>";
    $event->eventtype = 'course'; // Constant defined somewhere in your code - this can be any string value you want. It is a way to identify the event.
    $event->type = CALENDAR_EVENT_TYPE_STANDARD; // This is used for events we only want to display on the calendar, and are not needed on the block_myoverview.
    $event->name = $fromform->title;
    $event->description = $fromform->description."<br/><br/> Stream: ".$stream. "<br/><br/>Discuss here: ". $discussionlink;
    $event->format = FORMAT_HTML;
    $event->courseid = $courseid;
    $event->uuid = 'mootprogram';
    $event->groupid = 0;
    $event->userid = 0;
    $event->instance = $recordid;
    $event->timestart = $fromform->timestart;
    $event->visible = 1;
    $event->timeduration = ($fromform->length) * 60;

    if ($eventid) {

        $event->id = $eventid->id;
        $event->desription = [
            'format' => FORMAT_HTML,
            'text' => $event->description
        ];
        $eventforupdate->update($event, false);
    } else {
        calendar_event::create($event);
    }

    $imagename = $mform->get_new_filename('image');
    if ($imagename) {
        $draftid = file_get_submitted_draft_itemid('image');
        file_save_draft_area_files($draftid, $context->id, 'block_mootprogram', 'program', $recordid);
        $fs = new file_storage();
        $fs->delete_area_files($context->id, 'block_mootprogram', 'program', $recordid);
        $storedfile = $mform->save_stored_file('image', $context->id, 'block_mootprogram', 'program', $recordid, '/', $imagename);
        $imagefileid = $storedfile->get_id();
        $DB->set_field('block_mootprogram', 'image', $imagefileid, ['id' => $recordid]);
    }
    redirect(new moodle_url('/blocks/mootprogram/editschedule.php'), 'Successfully updated', 5);
}

if ($id) {
    $data = $DB->get_record('block_mootprogram', ['id' => $id]);
    if ($data) {
        if ($imageid = $data->image) {
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
        if (!empty($imageurl)) {
            $data->imageholder = "<img width='150px' height='150px' src='".$imageurl."'/>";
        } else {
            $data->imageholder = '';
        }

        $data->speakerholder = '';

        if ($data->userid) {
            $user = $DB->get_record('user', ['id' => $data->userid]);
            $data->speakerholder .= "<a href='".$CFG->wwwroot."/user/profile.php?id=".$data->userid."'>".$user->firstname. " ".$user->lastname."</a>";
            $userpicture = '';
            if ($user) {
                $userpic = new \user_picture($user);
                $userpic->size = true;
                $data->speakerholder .= "<br/><br/>".$OUTPUT->render($userpic, $PAGE);
            }

            $data->speakerholder .= '';
        }

        if ($happeningnowrecord->speakerlist) {
            $presenterlist = [];
            $speakers = explode(',', $happeningnowrecord->speakerlist);
            foreach ($speakers as $speaker) {
                $speakeruserid = $DB->get_field_select('user', 'id', $DB->sql_like($DB->sql_fullname(), ':speaker'), ['speaker' => $speaker]);
                if ($speakeruserid) {
                    $presenterlist[] = "<a href='".$CFG->wwwroot."/user/profile.php?id=".$speakeruserid."'>".$speaker."</a>";
                } else {
                    $presenterlist[] = $speaker;
                }
            }
            $data->speakerholder .= rtrim(implode(",", $presenterlist), ",");
        }


        /*$data->description = [
            'format' => 1,
            'text' => $data->description
        ];*/
        //die(print_object($data));

        $mform->set_data($data);
    }
}


$mform->display();

echo $OUTPUT->footer();