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

namespace block_mootprogram;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/blocks/mootprogram/lib.php');

/**
 * Class for the displaying the translation table.
 *
 * @package    tool
 * @subpackage translationmanager
 * @copyright  2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scheduletable extends \table_sql {

    /**
     * @var string language
     */
    protected $lang;

    protected $pagefilter;

    public function __construct($search = '') {
        parent::__construct('schedule-table');
        // Define the list of columns to show.
        $columns = ['title', 'room', 'user' , 'image', 'timestart', 'edit'];
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $headers = ['Title', 'Room', 'user', 'image',  'Time start', get_string('edit')];
        $this->define_headers($headers);
        $this->is_collapsible = false;
        $this->sortable(false);
        $this->use_pages = true;
        $this->search = $search;
    }

    public function col_edit($data) {
        $params = ['id' => $data->id];
        $url = new \moodle_url('edit.php', $params);
        $html = \html_writer::link($url, get_string("edit"));
        $params = ['delete' => $data->id];
        $url = new \moodle_url('editschedule.php', $params);
        $html .= '<br/>'.\html_writer::link($url, get_string("delete"));;

        return $html;
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;
        $params = [];
        $where = [];
        if ($this->search != '') {
            $where[] = $DB->sql_like('title', ':title', false);

            $params['title'] = "%".$DB->sql_like_escape($this->search)."%";
        }
        //$wherep[] = 'hidefromtable != 1';
        $wherestr = implode(" AND ", $where);
        $records = $DB->get_records_select('block_mootprogram', $wherestr, $params, 'timestart ASC', '*', $this->get_page_start(), $this->get_page_size());
        $total = $DB->count_records_select('block_mootprogram', $wherestr, $params);
        foreach ($records as $record) {
            $record->timestart = userdate($record->timestart);


            $fs = new \file_storage();
            $file = $fs->get_file_by_id($record->image);
            if ($file) {
                $record->imageurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out();
            }

            $record->user = \html_writer::link(new \moodle_url('/user/profile.php', ['id' => $record->userid]),
                $DB->get_field('user', $DB->sql_fullname(), ['id' => $record->userid]));

            $record->user .= "<br/><br/>". get_presenter_list($record);

            $record->image = "<img src='".$record->imageurl."' width='30px' height='30px'/>";

            $this->rawdata[] = $record;
        }
        $this->pagesize($pagesize, $total);
    }

}