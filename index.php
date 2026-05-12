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
 * Student Works - My Works page
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/studentworks/lib.php');

require_login();

$context = context_system::instance();

// Check access: student role in any course
if (!local_studentworks_is_student() && !local_studentworks_is_teacher()) {
    throw new moodle_exception('nopermissions', 'error', '', 'Access denied');
}

// Page setup using the standard function.
local_studentworks_page_setup(
    '/local/studentworks/index.php',
    [],
    'incourse',
    get_string('pluginname', 'local_studentworks'),
    get_string('pluginname', 'local_studentworks'),
    [
        'bodyclasses' => ['student-dashboard', 'limitedwidth-off'],
        'allowguest' => false,
        'context' => $context
    ]
);

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Load AMD module.
$PAGE->requires->js_call_amd('local_studentworks/studentworks', 'init', ['works']);

// Get user's works with all fields needed for fullname().
$sql = "SELECT sw.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, 
                u.middlename, u.alternatename, u.email
        FROM {local_studentworks} sw
        JOIN {user} u ON u.id = sw.userid
        WHERE sw.userid = :userid
        ORDER BY sw.timecreated DESC";

$records = $DB->get_records_sql($sql, ['userid' => $USER->id]);

// Render output.
$output = $PAGE->get_renderer('local_studentworks');
$page = new \local_studentworks\output\works_page(
    $records,
    new moodle_url('/local/studentworks/upload.php')
);

echo $OUTPUT->header();
echo $output->render($page);
echo $OUTPUT->footer();
