<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the HTML block.
 *
 * @param int $oldversion
 */
function xmldb_block_mootprogram_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019111801) {

        // Define table block_mootprogram to be created.
        $table = new xmldb_table('block_mootprogram');

        // Adding fields to table block_mootprogram.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('room', XMLDB_TYPE_CHAR, '200', null, null, null, null);
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('length', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('image', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('hightlight', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('discussionlink', XMLDB_TYPE_CHAR, '400', null, null, null, null);

        // Adding keys to table block_mootprogram.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_mootprogram.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2019111801, 'mootprogram');
    }

    if ($oldversion < 2019111802) {
        // Define field institute to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');

        $field = new xmldb_field('sponsoredevent', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'discussionlink');

        // Conditionally launch add field sponsoredevent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('institute', XMLDB_TYPE_CHAR, '300', null, null, null, null, 'sponsoredevent');

        // Conditionally launch add field institute.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2019111802, 'mootprogram');
    }

    if ($oldversion < 2019111803) {
        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2019111803, 'mootprogram');
    }

    if ($oldversion < 2019111804) {

        // Define field speakerlist to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('speakerlist', XMLDB_TYPE_CHAR, '500', null, null, null, null, 'institute');

        // Conditionally launch add field speakerlist.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2019111804, 'mootprogram');
    }

    if ($oldversion < 2020070700) {
        // Define field speakerlist to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('recordinglink', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'speakerlist');

        // Conditionally launch add field recordinglink.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2020070700, 'mootprogram');
    }

    if ($oldversion < 2020070702) {

        // Define table block_mootprogram_conference to be created.
        $table = new xmldb_table('block_mootprogram_conference');

        // Adding fields to table block_mootprogram_conference.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('tag', XMLDB_TYPE_CHAR, '155', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table block_mootprogram_conference.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_mootprogram_conference.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_mootprogram_timeslots to be created.
        $table = new xmldb_table('block_mootprogram_timeslots');

        // Adding fields to table block_mootprogram_timeslots.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('conferenceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessionlength', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_mootprogram_timeslots.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_mootprogram_timeslots.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_mootprogram_starred to be created.
        $table = new xmldb_table('block_mootprogram_starred');

        // Adding fields to table block_mootprogram_starred.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_mootprogram_starred.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('sessionidfk', XMLDB_KEY_FOREIGN, ['sessionid'], 'block_mootprogram', ['id']);
        $table->add_key('useridfk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Conditionally launch create table for block_mootprogram_starred.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2020070702, 'mootprogram');
    }

    if ($oldversion < 2020070703) {

        // Define field courseid to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'recordinglink');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field sessionslot to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('sessionslot', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'courseid');

        // Conditionally launch add field sessionslot.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field conferenceid to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('conferenceid', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'sessionslot');

        // Conditionally launch add field conferenceid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2020070703, 'mootprogram');
    }

    if ($oldversion < 2020070704) {

        // Define field sessionlink to be added to block_mootprogram.
        $table = new xmldb_table('block_mootprogram');
        $field = new xmldb_field('sessionlink', XMLDB_TYPE_CHAR, '500', null, null, null, null, 'conferenceid');

        // Conditionally launch add field sessionlink.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mootprogram savepoint reached.
        upgrade_block_savepoint(true, 2020070704, 'mootprogram');
    }

}