<?php

namespace block_mootprogram\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

class edit_session_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'conferenceid', $this->_customdata['conferenceid']);
        $mform->setType('conferenceid', PARAM_INT);


        $mform->addElement('date_time_selector', 'starttime', 'Start time');

        $mform->addElement('text', 'sessionlength', 'Session length');
        $mform->setType('sessionlength', PARAM_INT);

        $this->add_action_buttons();
    }
}