<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\navigation\primary_navigation_extend::class,
        'callback' => '\local_studentworks\hooks\navigation::extend_primary_navigation',
        'priority' => 0,
    ],
];
