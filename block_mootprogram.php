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

use core_course\external\course_summary_exporter;

require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');

class block_mootprogram extends block_base {
    function init() {
        $this->title = get_string('pluginname','block_mootprogram') ;
    }

    function has_config() {
        return true;
    }

    function hide_header() {
        return true;
    }

    function get_content() {
        global $OUTPUT, $DB, $PAGE, $CFG, $USER;
        $PAGE->requires->js_call_amd('block_mootprogram/program', 'init');
        require_once("$CFG->libdir/filestorage/file_storage.php");
        if ($this->content != null) {
            return $this->content;
        }
        $this->content = new stdClass;

        $data = [];

        if (is_siteadmin()) {
            $siteadmin = true;
        } else {
            $siteadmin = null;
        }

        $happeningnowrecords = $DB->get_records_select('block_mootprogram', 'timestart < ? AND timestart + (length * 60) > ? ',
            [time(), time()], 'timestart', '*',0, 4);
        foreach ($happeningnowrecords as $happeningnowrecord) {
            $happeningnowrecord->issiteadmin = $siteadmin;
            $data['happeningnowrecords'][$happeningnowrecord->id] = ($happeningnowrecord);
            if ($imageid = $happeningnowrecord->image) {
                $fs = new \file_storage();
                $file = $fs->get_file_by_id($imageid);
                if ($file) {
                    $data['happeningnowrecords'][$happeningnowrecord->id]->imageurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out();
                } else {
                    $data['happeningnowrecords'][$happeningnowrecord->id]->imageurl  = 'https://picsum.photos/20'.rand(0,9);
                }

            } else {
                $data['happeningnowrecords'][$happeningnowrecord->id]->imageurl  = 'https://picsum.photos/20'.rand(0,9);
            }
            $presenterlist = null;
            if ($happeningnowrecord->speakerlist) {
                $presenterlist = get_presenter_list($happeningnowrecord);
            }
            if ($happeningnowrecord->userid) {

                $user = $DB->get_record('user', ['id' => $happeningnowrecord->userid]);
                $userpicture = '';
                if ($user) {
                    $userpic = new \user_picture($user);
                    $userpic->size = true;
                    $userpictureurl = $userpic->get_url($PAGE)->out();
                    $data['happeningnowrecords'][$happeningnowrecord->id]->presentername = $user->firstname . ' ' . $user->lastname;
                    $data['happeningnowrecords'][$happeningnowrecord->id]->userpicture = $userpicture;
                    $data['happeningnowrecords'][$happeningnowrecord->id]->$userpictureurl = $userpictureurl;
                    $data['happeningnowrecords'][$happeningnowrecord->id]->profiledescription = $user->description;
                }
            }

            $courseid = ($happeningnowrecord->courseid);

            try {
                $roomname = get_course($courseid)->fullname;
            } catch (dml_exception $e) {
                $roomname = get_string('session', 'block_mootprogram');
            }

            $url = new moodle_url('/course/view.php', ['id' => $courseid]);
            if (isset($happeningnowrecord->sessionlink) && $happeningnowrecord->sessionlink!='') {
                $sessionurl = $happeningnowrecord->sessionlink;
            } else {
                $sessionurl = $url->out(false);
            }
            $eurl = new moodle_url('/blocks/mootprogram/edit.php', ['id' => $happeningnowrecord->id]);
            $editurl = $eurl->out(false);
            $uurl = new moodle_url('/user/profile.php', ['id' => $happeningnowrecord->userid]);
            $userurl = $uurl->out(false);
            $forumid = forum_id_mapper($happeningnowrecord);
            if ($forumid !== 0) {
                $durl = new moodle_url('/mod/forum/view.php', ['id' => $forumid]);

                $data['happeningnowrecords'][$happeningnowrecord->id]->discussionlink = $happeningnowrecord->discussionlink === '' ? $durl->out(false) : $happeningnowrecord->discussionlink;
            }

            if ($DB->record_exists('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $happeningnowrecord->id])) {
                $data['happeningnowrecords'][$happeningnowrecord->id]->isStared = true;
            }

            $data['happeningnowrecords'][$happeningnowrecord->id]->presenterlist = $presenterlist;
            $data['happeningnowrecords'][$happeningnowrecord->id]->sessionurl = $sessionurl;
            $data['happeningnowrecords'][$happeningnowrecord->id]->editUrl = $editurl;
            $data['happeningnowrecords'][$happeningnowrecord->id]->userLink = $userurl;
            $data['happeningnowrecords'][$happeningnowrecord->id]->roomName = $roomname;
            $data['happeningnowrecords'][$happeningnowrecord->id]->institute = $happeningnowrecord->institute;
            $data['happeningnowrecords'][$happeningnowrecord->id]->timeend = $happeningnowrecord->timeend = trim($happeningnowrecord->timestart + ($happeningnowrecord->length * 60));
        }

        $upcomingrecords = $DB->get_records_select('block_mootprogram', 'timestart > ?', [time()], 'timestart', '*',0, 6);
        foreach ($upcomingrecords as $upcomingrecord) {
            $upcomingrecord->issiteadmin = $siteadmin;
            $data['upcomingrecords'][$upcomingrecord->id] = ($upcomingrecord);
            if ($imageid = $upcomingrecord->image) {
                $fs = new \file_storage();
                $file = $fs->get_file_by_id($imageid);
                if ($file) {
                    $data['upcomingrecords'][$upcomingrecord->id]->imageurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out();
                } else {
                    $data['upcomingrecords'][$upcomingrecord->id]->imageurl  = 'https://picsum.photos/20'.rand(0,9);
                }

            } else {
                $data['upcomingrecords'][$upcomingrecord->id]->imageurl  = 'https://picsum.photos/20'.rand(0,9);
            }

            $presenterlist = null;
            if ($upcomingrecord->speakerlist) {
                $presenterlist = get_presenter_list($upcomingrecord);
            }

            if ($upcomingrecord->userid) {

                $user = $DB->get_record('user', ['id' => $upcomingrecord->userid]);
                $userpicture = '';
                if ($user) {
                    $userpic = new \user_picture($user);
                    $userpic->size = true;
                    $userpictureurl = $userpic->get_url($PAGE)->out();
                    $data['upcomingrecords'][$upcomingrecord->id]->presentername = $user->firstname . ' ' . $user->lastname;
                    $data['upcomingrecords'][$upcomingrecord->id]->userpicture = $userpicture;
                    $data['upcomingrecords'][$upcomingrecord->id]->userpictureurl = $userpictureurl;
                    $data['upcomingrecords'][$upcomingrecord->id]->profiledescription = $user->description;
                }
            }

            if ($DB->record_exists('block_mootprogram_starred', ['userid' => $USER->id, 'sessionid' => $upcomingrecord->id])) {
                $data['upcomingrecords'][$upcomingrecord->id]->isStared = true;
            }

            $courseid = ($upcomingrecord->courseid);

            try {
                $roomname = get_course($courseid)->fullname;
            } catch (dml_exception $e) {
                $roomname = get_string('session', 'block_mootprogram');
            }

            $url = new moodle_url('/course/view.php', ['id' => $courseid]);
            if (isset($upcomingrecord->sessionlink) && $upcomingrecord->sessionlink!='') {
                $sessionurl = $upcomingrecord->sessionlink;
            } else {
                $sessionurl = $url->out(false);
            }
            $eurl = new moodle_url('/blocks/mootprogram/edit.php', ['id' => $upcomingrecord->id]);
            $editurl = $eurl->out(false);
            $uurl = new moodle_url('/user/profile.php', ['id' => $upcomingrecord->userid]);
            $userurl = $uurl->out(false);
            $forumid = forum_id_mapper($upcomingrecord);
            if ($forumid !== 0) {
                $durl = new moodle_url('/mod/forum/view.php', ['id' => $forumid]);

                $data['upcomingrecords'][$upcomingrecord->id]->discussionlink = $upcomingrecord->discussionlink === '' ? $durl->out(false) : $upcomingrecord->discussionlink;
            }

            $data['upcomingrecords'][$upcomingrecord->id]->presenterlist = $presenterlist;
            $data['upcomingrecords'][$upcomingrecord->id]->sessionurl = $sessionurl;
            $data['upcomingrecords'][$upcomingrecord->id]->editUrl = $editurl;
            $data['upcomingrecords'][$upcomingrecord->id]->userLink = $userurl;
            $data['upcomingrecords'][$upcomingrecord->id]->roomName = $roomname;
            $data['upcomingrecords'][$upcomingrecord->id]->institute = $upcomingrecord->institute;
            $data['upcomingrecords'][$upcomingrecord->id]->timeend = $upcomingrecord->timeend = trim($upcomingrecord->timestart + ($upcomingrecord->length * 60));
        }

        $url = new moodle_url('/blocks/mootprogram/schedule.php#'.get_config('block_mootprogram')->day);
        $schedulelink = $url->out(false);

        $nowclasses = presentation_classes(!empty($data['happeningnowrecords']) ? count($data['happeningnowrecords']) : 0);
        $upcomingclasses = presentation_classes(!empty($data['upcomingrecords'])? count($data['upcomingrecords']) : 0);

        $surl = new moodle_url('/course/view.php', ['id' => 91]);
        $sponserurl = $surl->out(false);
        $curl = new moodle_url('/course/view.php', ['id' => 95]);
        $cafeurl = $curl->out(false);

        $this->content->text =  $OUTPUT->render_from_template('block_mootprogram/programblock', [
            'happeningnowrecord' => !empty($data['happeningnowrecords']) ? array_values($data['happeningnowrecords']) : [],
            'upcomingrecord' => !empty($data['upcomingrecords']) ? array_values($data['upcomingrecords']) : [],
            'multiplehappeningnow' => !empty($data['happeningnowrecords']) && count($data['happeningnowrecords']) > 1 ? true : false,
            'multipleupcoming' => !empty($data['upcomingrecords']) && count($data['upcomingrecords']) > 1 ? true : false,
            'sponsordesc' => $DB->get_field('course', 'summary', ['id' => 91]),
            'networkingdesc' => $DB->get_field('course', 'summary', ['id' => 95]),
            'issiteadmin' => is_siteadmin(),
            'sponserImg' => course_summary_exporter::get_course_image(get_course(91)),
            'cafeImg' => course_summary_exporter::get_course_image(get_course(95)),
            'sponserLink' => $sponserurl,
            'networkLink' => $cafeurl,
            'presentationsNow' => count($happeningnowrecords) > 0 ? true : false,
            'scheduleLink' => $schedulelink,
            'nowClasses' => $nowclasses,
            'upcomingClasses' => $upcomingclasses,
        ]);
        $PAGE->requires->js_call_amd('block_mootprogram/program', 'init');
        return $this->content;
    }
}
