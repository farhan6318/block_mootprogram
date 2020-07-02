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
/**
 * Mobile output functions.
 */
class mobile {

    /**
     * Returns the SC document view page for the mobile app.
     *
     * @param array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_block_view(array $args) : array {
        global $OUTPUT, $DB;

        $happeningnowrecorddata = [];

        $happeningnowrecords = $DB->get_records_select('block_mootprogram', 'timestart > ?', [time() - HOURSECS], 'timestart', '*',0, 4);
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

            //$imageurl = 'https://picsum.photos/202';
            $happeningnowrecorddata[] = [
                'title' => $happeningnowrecord->title,
                'description' => (strlen($happeningnowrecord->description) > 100) ? $happeningnowrecord->description : substr($happeningnowrecord->description, 0, 100)."...",
                'presenter' => ($happeningnowrecord->userid) ? $DB->get_field('user', $DB->sql_fullname(), ['id' => $happeningnowrecord->userid]) : '',
                'link' => $sessionurl,
                'institute' => $happeningnowrecord->institute,
                'image' => '<img src="'.$imageurl.  '" width="100%" height="150px"/>',
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
