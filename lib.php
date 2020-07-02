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