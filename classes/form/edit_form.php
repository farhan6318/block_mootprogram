<?php

namespace block_mootprogram\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

class edit_form extends moodleform {
    function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'title', 'Title');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('select', 'userid', 'User',  $DB->get_records_menu('user', ['deleted' => 0], $DB->sql_fullname(), 'id, '.$DB->sql_fullname()));

        $mform->addElement('text', 'speakerlist', 'Speakerlist', ['size' => 50]);
        $mform->setType('speakerlist', PARAM_TEXT);

        $mform->addElement('static', 'speakerholder', '');

        /*$mform->addElement('select', 'room', 'Room', ['Education' => 'Education', 'Technology' => 'Technology', 'Quiet' => 'Quiet',
        'Chinese' => 'Chinese', 'Spanish' => 'Spanish', 'German' => 'German', 'French' => 'French', 'Networking Cafe' => 'Networking Cafe',
            'Sponsor Solutions' => 'Sponsor Solutions', 'Treasure Hunt' => 'Treasure Hunt']);
        $mform->setType('room', PARAM_TEXT);

        $mform->addElement('date_time_selector', 'timestart', 'Time start');

        $mform->addElement('text', 'length', 'Length');
        $mform->setType('length', PARAM_INT);*/

        $mform->addElement('select', 'conferenceid', 'Conference',
            array_merge([0 => 'Choose'], $DB->get_records_menu('block_mootprogram_conference', [], '', 'id, name')));

        $mform->addElement('select', 'courseid', 'Course id',
            array_merge([0 => 'Choose'], $DB->get_records_menu('course', ['visible' => 1], '', 'id, fullname')));

        $records = $DB->get_records_menu('block_mootprogram_timeslots', [], '', 'id, starttime');
        $slots = [];
        foreach ($records as $key => $value) {
            $slots[$key] = userdate($value);
        }
        $mform->addElement('select', 'sessionslot', 'Session slot',
            array_merge([0 => 'Choose'], $slots));


        $mform->addElement('textarea', 'description', 'Description', ["rows"=>"10", "cols"=>"40"]);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('text', 'institute', 'Institute');
        $mform->setType('institute', PARAM_TEXT);


        $mform->addElement('text', 'discussionlink', 'Discussion link', ['size' => 45]);
        $mform->setType('discussionlink', PARAM_RAW);

        $mform->addElement('text', 'recordinglink', 'Recording link', ['size' => 45]);
        $mform->setType('recordinglink', PARAM_RAW);

        $mform->addElement('checkbox', 'hightlight', 'Highlight');
        $mform->addElement('checkbox', 'sponsoredevent', 'Sponsoredevent');

        $mform->addElement('static', 'imageholder', '');

        $mform->addElement('filepicker', 'image', 'Image');

        $this->add_action_buttons();
    }
}