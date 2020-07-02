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

class block_mootprogram extends block_base {
    function init() {
        $this->title = get_string('pluginname','block_mootprogram') ;
    }

    function has_config() {
        return false;
    }

    function get_content() {
        global $OUTPUT, $DB, $PAGE;
        $this->content = new stdClass;

        $data = [];
        /*$highlightrecords = $DB->get_records_select('block_mootprogram', 'timestart > ? AND hightlight = 1', [time() - HOURSECS], 'timestart', '*',0, 3);
        foreach ($highlightrecords as $highlightrecord) {
            $data['highlightrecords'][$highlightrecord->id] = ($highlightrecord);
            if ($imageid = $highlightrecord->image) {
                $fs = new \file_storage();
                $file = $fs->get_file_by_id($imageid);
                if ($file) {
                    $data['highlightrecords'][$highlightrecord->id]->imageurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out();
                }
            }
            if ($highlightrecord->userid) {
                $user = $DB->get_record('user', ['id' => $highlightrecord->userid]);
                // User image.
                $userpicture = '';
                if ($user) {
                    $userpic = new \user_picture($user);
                    // If the user has uploaded a profile picture, use it.
                    if (!empty($userpic->user->picture)) {
                        $userpic->link = false;
                        $userpic->alttext = false;
                        $userpic->size = 128;
                        $userpic->visibletoscreenreaders = false;
                        $userpic->class = "mr-5";
                        $userpicture = $OUTPUT->render($userpic);
                    }
                    $data['highlightrecords'][$highlightrecord->id]->presentername = $user->firstname . ' ' . $user->lastname;
                    $data['highlightrecords'][$highlightrecord->id]->userpicture = $userpicture;
                    $data['highlightrecords'][$highlightrecord->id]->profiledescription = $user->description;
                }
            }
            if ($highlightrecord->room == 'Learning') {
                $sessionurl = "https://events.moodle.com/course/view.php?id=40";
            } else if ($highlightrecord->room == 'Technology') {
                $sessionurl = "https://events.moodle.com/course/view.php?id=41";
            } else if ($highlightrecord->room == 'Quiet') {
                $sessionurl = "https://events.moodle.com/course/view.php?id=50";
            } else if ($highlightrecord->room == 'Language') {
                $sessionurl = "https://events.moodle.com/course/view.php?id=53";
            }
            $data['highlightrecords'][$highlightrecord->id]->sessionurl = $sessionurl;
        }*/

        if (is_siteadmin()) {
            $siteadmin = true;
        } else {
            $siteadmin = null;
        }

        $happeningnowrecords = $DB->get_records_select('block_mootprogram', 'timestart > ?', [time() - (HOURSECS / 2)], 'timestart', '*',0, 4);
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

            if ($happeningnowrecord->room == 'Education') {
                $courseid = 40;
            } else if ($happeningnowrecord->room == 'Technology') {
                $courseid = 41;
            } else if ($happeningnowrecord->room == 'Quiet') {
                $courseid = 50;
            } else if ($happeningnowrecord->room == 'Chinese') {
                $courseid = 49;
            } else if ($happeningnowrecord->room == 'Spanish') {
                $courseid = 51;
            } else if ($happeningnowrecord->room == 'German') {
                $courseid = 52;
            }  else if ($happeningnowrecord->room == 'French') {
                $courseid = 53;
            }

            $sessionurl = "https://events.moodle.com/course/view.php?id=".$courseid;

            $data['happeningnowrecords'][$happeningnowrecord->id]->sessionurl = $sessionurl;
        }

        $upcomingrecords = $DB->get_records_select('block_mootprogram', 'timestart > ?', [time() + (HOURSECS / 2)], 'timestart', '*',0, 8);
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
            if ($upcomingrecord->userid) {

                $user = $DB->get_record('user', ['id' => $upcomingrecord->userid]);
                $userpicture = '';
                if ($user) {
                    $userpic = new \user_picture($user);
                    $userpic->size = true;
                    $userpictureurl = $userpic->get_url($PAGE)->out();
                    $data['upcomingrecords'][$upcomingrecord->id]->presentername = $user->firstname . ' ' . $user->lastname;
                    $data['upcomingrecords'][$upcomingrecord->id]->userpicture = $userpicture;
                    $data['upcomingrecords'][$upcomingrecord->id]->$userpictureurl = $userpictureurl;
                    $data['upcomingrecords'][$upcomingrecord->id]->profiledescription = $user->description;
                }
            }

            if ($upcomingrecord->room == 'Education') {
                $courseid = 40;
            } else if ($upcomingrecord->room == 'Technology') {
                $courseid = 41;
            } else if ($upcomingrecord->room == 'Quiet') {
                $courseid = 50;
            } else if ($upcomingrecord->room == 'Chinese') {
                $courseid = 49;
            } else if ($upcomingrecord->room == 'Spanish') {
                $courseid = 51;
            } else if ($upcomingrecord->room == 'German') {
                $courseid = 52;
            }  else if ($upcomingrecord->room == 'French') {
                $courseid = 53;
            }

            $sessionurl = "https://events.moodle.com/course/view.php?id=".$courseid;

            $data['upcomingrecords'][$upcomingrecord->id]->sessionurl = $sessionurl;
        }

        //die(print_object(array_values($data['upcomingrecords'])));

        $this->content->text =  $OUTPUT->render_from_template('block_mootprogram/programblock', [
            'happeningnowrecord' => array_values($data['happeningnowrecords']),
            'upcomingrecord' => array_values($data['upcomingrecords']),
            'sponsordesc' => $DB->get_field('course', 'summary', ['id' => 43]),
            'networkingdesc' => $DB->get_field('course', 'summary', ['id' => 42]),
            'issiteadmin' => is_siteadmin()
        ]);

        //$this->content->text = "testing block";
        return $this->content;
    }
}
