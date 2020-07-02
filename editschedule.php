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

require('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$PAGE->set_context($context);
$url = new moodle_url('/blocks/mootprogram/editschedule.php');
$PAGE->set_url($url);
$title = 'Edit Schedule';
$PAGE->set_heading($title);
$PAGE->set_title($title);

echo $OUTPUT->header();
if ($delete) {
    if (!$confirm) {
        echo $OUTPUT->confirm('Confirm delete?',
            '?delete='.$delete.'&confirm=1', '?delete=0&confirm=0');
    } else {
       $DB->delete_records('block_mootprogram', ['id' => $delete]);
        $eventid = $DB->get_record('event', ['uuid' => 'mootprogram', 'instance' => $delete]);
        if ($eventid) {
            $event = calendar_event::load($eventid);
            $event->delete();
        }
        \core\notification::success(get_string('deleted'));
    }
}

echo $OUTPUT->heading(get_string('pluginname', 'block_mootprogram'));

$search = '';
$mform = new search_form();

if ($fromform = $mform->get_data()) {
    //print_object($fromform);
    $search = $fromform->search;
}
$mform->display();
echo $OUTPUT->single_button(new moodle_url('/blocks/mootprogram/edit.php', []),
    'Add new presentation');
$translationtable = new block_mootprogram\scheduletable($search);
$translationtable->pagesize = 200;
$translationtable->define_baseurl(new moodle_url('/blocks/mootprogram/editschedule.php'));

$translationtable->out(200, false);
echo $OUTPUT->footer();