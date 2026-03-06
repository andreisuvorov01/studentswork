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

$id = required_param('id', PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/studentworks/view.php', ['id' => $id]);
$PAGE->set_title(get_string('viewwork', 'local_studentworks'));
$PAGE->set_heading(get_string('viewwork', 'local_studentworks'));
$PAGE->set_pagelayout('standard');

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Load AMD module.
$PAGE->requires->js_call_amd('local_studentworks/studentworks', 'init', ['detail']);

// Get work data.
$work = $DB->get_record('local_studentworks', ['id' => $id], '*', MUST_EXIST);
$author = $DB->get_record('user', ['id' => $work->userid], '*', MUST_EXIST);

// Check permissions: by capability, course role, or ownership.
$canviewall = has_capability('local/studentworks:viewall', $context) ||
              \local_studentworks_has_teacher_access($USER->id, $context);
$isowner = $USER->id == $work->userid;
$hasstudentaccess = has_capability('local/studentworks:viewown', $context) ||
                    \local_studentworks_has_student_access($USER->id, $context);

if (!$canviewall && !$isowner && !$hasstudentaccess) {
    throw new moodle_exception('nopermissions', 'error');
}

// If user only has student access, they can only view their own works.
if (!$canviewall && $hasstudentaccess && !$isowner) {
    throw new moodle_exception('nopermissions', 'error');
}

// Get files.
$fs = get_file_storage();
$workfiles = $fs->get_area_files($context->id, 'local_studentworks', 'workfile', $id, 'filename', false);
$reviewfiles = $fs->get_area_files($context->id, 'local_studentworks', 'reviewfile', $id, 'filename', false);

$workfile = reset($workfiles);
$reviewfile = reset($reviewfiles);

// Determine back URL.
$backurl = $canviewall
    ? new moodle_url('/local/studentworks/teacher.php')
    : new moodle_url('/local/studentworks/index.php');

// Can review if user has viewall capability and is not the owner.
$canreview = $canviewall && !$isowner;

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
