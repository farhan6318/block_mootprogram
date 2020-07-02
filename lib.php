<?php

function block_mootprogram_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    $itemid = array_shift($args); // Ignore revision - designed to prevent caching problems only...

    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/block_mootprogram/$filearea/$itemid/$relativepath";
    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Force download.
    send_stored_file($file, 0, 0, true);
}

/**
 * For a given presentation figure out the course ID.
 *
 * @param string $presentation The presentation object from the DB.
 * @return int Course ID.
 */
function course_id_mapper(stdClass $presentation) {
    if ($presentation->room == 'Education') {
        return 40;
    } else if ($presentation->room == 'Technology') {
        return 41;
    } else if ($presentation->room == 'Quiet') {
        return 50;
    } else if ($presentation->room == 'Chinese') {
        return 49;
    } else if ($presentation->room == 'Spanish') {
        return 51;
    } else if ($presentation->room == 'German') {
        return 52;
    }  else if ($presentation->room == 'French') {
        return 53;
    }
}
