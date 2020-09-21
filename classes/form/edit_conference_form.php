<?php

namespace block_mootprogram\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

class edit_conference_form extends moodleform {
    function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'tag', 'Tag');
        $mform->setType('tag', PARAM_TEXT);

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('date_selector', 'startdate', 'Start date');

        $mform->addElement('date_selector', 'enddate', 'End date');

        $mform->addElement('select', 'categoryid', 'Category', $DB->get_records_menu('course_categories', [], '', 'id, name'));

        $this->add_action_buttons();
    }
}