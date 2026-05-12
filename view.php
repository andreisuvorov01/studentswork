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
 * Student Works - View Work Detail
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/studentworks/lib.php');

require_login();

$context = context_system::instance();

// Get work ID and data
$params = local_studentworks_page_setup(
    '/local/studentworks/view.php',
    [],
    'incourse',
    get_string('viewwork', 'local_studentworks'),
    get_string('viewwork', 'local_studentworks'),
    [
        'bodyclasses' => ['work-detail-view', 'limitedwidth-off'],
        'allowguest' => false,
        'paramdefinitions' => [
            ['name' => 'id', 'type' => PARAM_INT, 'required' => true]
        ]
    ]
);

$id = $params['id'];

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Get work data.
$work = $DB->get_record('local_studentworks', ['id' => $id], '*', MUST_EXIST);
$author = $DB->get_record('user', ['id' => $work->userid], '*', MUST_EXIST);

// Check permissions: teacher can view all, student can view only own
$isteacher = local_studentworks_is_teacher();
$isowner = $USER->id == $work->userid;

if (!$isteacher && !$isowner) {
    throw new moodle_exception('nopermissions', 'error', '', 'Access denied');
}

// Get files.
$fs = get_file_storage();
$workfiles = $fs->get_area_files($context->id, 'local_studentworks', 'workfile', $id, 'filename', false);
$reviewfiles = $fs->get_area_files($context->id, 'local_studentworks', 'reviewfile', $id, 'filename', false);

$workfile = reset($workfiles);
$reviewfile = reset($reviewfiles);

// Determine back URL.
$backurl = $isteacher
    ? new moodle_url('/local/studentworks/teacher.php')
    : new moodle_url('/local/studentworks/index.php');

// Can review if user is teacher and not the owner.
$canreview = $isteacher && !$isowner;

// Render output.
$output = $PAGE->get_renderer('local_studentworks');
$detail = new \local_studentworks\output\work_detail(
    $work,
    $author,
    $workfile ?: null,
    $reviewfile ?: null,
    $canreview,
    $backurl
);

echo $OUTPUT->header();
echo $output->render($detail);
echo $OUTPUT->footer();
