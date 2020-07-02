<?php

require_once(__DIR__.'/../../config.php');
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
echo $OUTPUT->header();
$DB->delete_records('block_mootprogram');
$DB->delete_records('event', ['uuid' => 'mootprogram']);
echo "all cleared";
echo $OUTPUT->footer();