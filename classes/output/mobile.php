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
 * Mobile output functions.
 *
 * @package mod_oucontent
 * @copyright 2018 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mootprogram\output;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_storage.php");
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');
/**
 * Mobile output functions.
 */
class mobile {

    public static function mobile_schedule_view(array $args) : array {
        global $OUTPUT, $DB;
        $happeningnowrecorddata = [];

        $args = (object) $args;

        $dates = $DB->get_records_sql("SELECT DISTINCT TO_CHAR(to_timestamp(timestart), 'DDMMYYYY') as timestamps, TO_CHAR(to_timestamp(timestart), 'Day') as days from {block_mootprogram}
ORDER BY timestamps");
        $days = [];
        foreach ($dates as $date) {
            $days[] = [
                'day' => $date->days,
                'timestamp' =>$date->timestamps
            ];
        }

        if ($args->first) {
            $param = reset($days)['timestamp'];
        } else {
            $param = $args->timestamp;
        }

        $sql = "SELECT p.*, ".$DB->sql_fullname()." as presentername
                  FROM {block_mootprogram} p
              LEFT JOIN {user} u ON u.id = p.userid
                  WHERE ".$DB->sql_like("TO_CHAR(to_timestamp(timestart), 'DDMMYYYY')", ":param")."
                ORDER BY timestart";

        $happeningnowrecords = $DB->get_records_sql($sql, ['param' => $param]);
        foreach ($happeningnowrecords as $happeningnowrecord) {

            if ($imageid = $happeningnowrecord->image) {
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
                } else {
                    $imageurl  = 'https://picsum.photos/20'.rand(0,9);
                }

            } else {
                $imageurl  = 'https://picsum.photos/20'.rand(0,9);
            }

            $courseid = course_id_mapper($happeningnowrecord);

            $sessionurl = "https://events.moodle.com/course/view.php?id=".$courseid;

            $presenterlist = get_presenter_list($happeningnowrecord);
            if ($presenterlist) {
                $presenter = $presenterlist;
            } else {
                $presenter = \html_writer::link(new \moodle_url('/user/profile.php', ['id' => $happeningnowrecord->userid]),
                    $DB->get_field('user', $DB->sql_fullname(), ['id' => $happeningnowrecord->userid]));
            }

            try {
                $roomname = get_course($courseid)->fullname;
            } catch (dml_exception $e) {
                $roomname = get_string('session', 'block_mootprogram');
            }


            $happeningnowrecorddata[] = [
                'title' => $happeningnowrecord->title,
                'description' => (strlen($happeningnowrecord->description) > 100) ? $happeningnowrecord->description : substr($happeningnowrecord->description, 0, 100)."...",
                'presenter' => $presenter,
                'link' => $sessionurl,
                'institute' => $happeningnowrecord->institute,
                'discussionlink' => $happeningnowrecord->discussionlink,
                'roomname' => $roomname,
                'timestart' => $happeningnowrecord->timestart,
                'timeend' => trim($happeningnowrecord->timestart + ($happeningnowrecord->length * 60)),
                'image' => '<img src="'.$imageurl.  '" width="100%" height="150px"/>'
            ];
        }

        $data = [
            'days' => $days,
            'presentations' => $happeningnowrecorddata
        ];
        return [
            'templates' => [
                [
                    'id' => 'mootprogram',
                    'html' => $OUTPUT->render_from_template('block_mootprogram/mobile_schedule_view', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => [],
            'files' => []
        ];
    }

    /**
     * Returns the SC document view page for the mobile app.
     *
     * @param array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_block_view(array $args) : array {
        global $OUTPUT, $DB;

        $happeningnowrecorddata = [];

        $happeningnowrecords = $DB->get_records_select('block_mootprogram', 'timestart < ? AND timestart + (length * 60) > ? ',
            [time(), time()], 'timestart', '*',0, 4);
        foreach ($happeningnowrecords as $happeningnowrecord) {

            if ($imageid = $happeningnowrecord->image) {
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
                } else {
                    $imageurl  = 'https://picsum.photos/20'.rand(0,9);
                }

            } else {
                $imageurl  = 'https://picsum.photos/20'.rand(0,9);
            }

            $courseid = course_id_mapper($happeningnowrecord);

            $sessionurl = "https://events.moodle.com/course/view.php?id=".$courseid;

            $presenterlist = get_presenter_list($happeningnowrecord);
            if ($presenterlist) {
                $presenter = $presenterlist;
            } else {
                $presenter = \html_writer::link(new \moodle_url('/user/profile.php', ['id' => $happeningnowrecord->userid]),
                    $DB->get_field('user', $DB->sql_fullname(), ['id' => $happeningnowrecord->userid]));
            }

            try {
                $roomname = get_course($courseid)->fullname;
            } catch (dml_exception $e) {
                $roomname = get_string('session', 'block_mootprogram');
            }


            $happeningnowrecorddata[] = [
                'title' => $happeningnowrecord->title,
                'description' => (strlen($happeningnowrecord->description) > 100) ? $happeningnowrecord->description : substr($happeningnowrecord->description, 0, 100)."...",
                'presenter' => $presenter,
                'link' => $sessionurl,
                'institute' => $happeningnowrecord->institute,
                'discussionlink' => $happeningnowrecord->discussionlink,
                'roomname' => $roomname,
                'timestart' => $happeningnowrecord->timestart,
                'timeend' => trim($happeningnowrecord->timestart + ($happeningnowrecord->length * 60)),
                'image' => '<img src="'.$imageurl.  '" width="100%" height="150px"/>'
            ];
        }

        $data = [
            'presentations' => $happeningnowrecorddata
        ];
        return [
            'templates' => [
                [
                    'id' => 'mootprogram',
                    'html' => $OUTPUT->render_from_template('block_mootprogram/mobile_block_view', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => [],
            'files' => []
        ];
    }
}