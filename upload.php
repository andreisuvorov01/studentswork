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
 * Student Works - Upload Work
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/studentworks/lib.php');

require_login();

$context = context_system::instance();

// Get parameters first.
$id = optional_param('id', 0, PARAM_INT);
$review = optional_param('review', 0, PARAM_BOOL);

// Check access based on action
if (!$id && !$review) {
    // New work upload - student or teacher
    if (!local_studentworks_is_student() && !local_studentworks_is_teacher()) {
        throw new moodle_exception('nopermissions', 'error', '', 'Access denied');
    }
} else if ($review) {
    // Review upload - teacher only
    if (!local_studentworks_is_teacher()) {
        throw new moodle_exception('nopermissions', 'error', '', 'Access denied - teacher role required');
    }
}

// Set up the page using the standard page setup function.
// Note: capabilities are checked explicitly above, so we don't pass them here.
// This avoids the OR logic issue in page_setup.
$filteredparams = local_studentworks_page_setup(
    '/local/studentworks/upload.php',
    ['id' => $id, 'review' => $review],
    'standard',
    get_string('uploadwork', 'local_studentworks'),
    get_string('uploadwork', 'local_studentworks'),
    [
        'bodyclasses' => ['work-upload-page'],
        'allowguest' => false,
        'paramdefinitions' => [
            ['name' => 'id', 'type' => PARAM_INT, 'required' => false, 'default' => 0],
            ['name' => 'review', 'type' => PARAM_BOOL, 'required' => false, 'default' => 0]
        ]
    ]
);

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Handle review upload.
if ($review && $id) {
    $work = $DB->get_record('local_studentworks', ['id' => $id], '*', MUST_EXIST);
    $PAGE->set_title(get_string('uploadreview', 'local_studentworks'));
    $PAGE->set_heading(get_string('uploadreview', 'local_studentworks'));

    $cancelurl = new moodle_url('/local/studentworks/teacher.php');
    $formaction = new moodle_url('/local/studentworks/upload.php', ['id' => $id, 'review' => 1]);
    $mform = new \local_studentworks\form\review_form($formaction->out(false), ['workid' => $id]);

    if ($mform->is_cancelled()) {
        redirect($cancelurl);
    }

    if ($data = $mform->get_data()) {
        file_save_draft_area_files(
            $data->reviewfile,
            $context->id,
            'local_studentworks',
            'reviewfile',
            $id,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        // Update status, grade and timestamp.
        $work->timemodified = time();
        $work->status = \local_studentworks\manager\work_manager::STATUS_REVIEWED;
        // Save grade if provided (empty string means no grade).
        if (isset($data->grade) && $data->grade !== '') {
            $work->grade = (int)$data->grade;
        }
        $DB->update_record('local_studentworks', $work);

        redirect(
            new moodle_url('/local/studentworks/view.php', ['id' => $id]),
            get_string('reviewsuccess', 'local_studentworks'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    echo $OUTPUT->header();

    // Back button.
    echo html_writer::start_div('sw-container');
    echo html_writer::start_div('sw-header', ['role' => 'banner', 'style' => 'padding: var(--sw-space-5) var(--sw-space-6);']);
    echo html_writer::start_div('sw-header-content');
    echo html_writer::link(
        new moodle_url('/local/studentworks/view.php', ['id' => $id]),
        '<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . get_string('back', 'local_studentworks'),
        ['class' => 'sw-btn sw-btn-ghost sw-btn-sm', 'style' => 'margin-bottom: var(--sw-space-2);']
    );
    echo html_writer::tag('h1', get_string('uploadreview', 'local_studentworks'), ['class' => 'sw-title', 'style' => 'font-size: 1.75rem;']);
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Form card.
    echo html_writer::start_div('sw-info-card', ['style' => 'max-width: 800px;']);
    $mform->display();
    echo html_writer::end_div();

    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

// Handle new work upload.
$PAGE->set_title(get_string('uploadwork', 'local_studentworks'));
$PAGE->set_heading(get_string('uploadwork', 'local_studentworks'));

$cancelurl = new moodle_url('/local/studentworks/index.php');
$formaction = new moodle_url('/local/studentworks/upload.php');
$mform = new \local_studentworks\form\studentwork_form($formaction->out(false), null);

if ($mform->is_cancelled()) {
    redirect($cancelurl);
}

error_log('[SW] Before get_data check');

if ($data = $mform->get_data()) {
    error_log('[SW] Form submitted by USER->id: ' . $USER->id);
    
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->worktype = $data->worktype;
    $record->topic = trim($data->topic);
    $record->discipline = trim($data->discipline);
    $record->timecreated = time();
    $record->timemodified = time();
    $record->status = \local_studentworks\manager\work_manager::STATUS_SUBMITTED;

    $id = $DB->insert_record('local_studentworks', $record);
    error_log('[SW] Record created, ID: ' . $id);

    // Notify teachers about new work.
    $work = $DB->get_record('local_studentworks', ['id' => $id]);
    $notifier = new \local_studentworks\notification\notifier();
    $notifier->notify_new_work($work);

    try {
        file_save_draft_area_files(
            $data->workfile,
            $context->id,
            'local_studentworks',
            'workfile',
            $id,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        // Verify file was saved.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_studentworks', 'workfile', $id, 'filename', false);
        if (empty($files)) {
            error_log('[SW] ERROR: No files saved');
            throw new moodle_exception('fileuploaderror', 'local_studentworks');
        }
        error_log('[SW] Success: ' . count($files) . ' file(s) saved');
    } catch (Exception $e) {
        error_log('[SW] Exception: ' . $e->getMessage());
        // Delete record on file upload error.
        $DB->delete_records('local_studentworks', ['id' => $id]);
        throw new moodle_exception('fileuploaderror', 'local_studentworks');
    }

    error_log('[SW] Redirecting to index.php');
    redirect(
        new moodle_url('/local/studentworks/index.php'),
        get_string('uploadsuccess', 'local_studentworks'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();

// Render with custom styling.
echo html_writer::start_div('sw-container');

// Header.
echo html_writer::start_div('sw-header', ['role' => 'banner', 'style' => 'padding: var(--sw-space-5) var(--sw-space-6);']);
echo html_writer::start_div('sw-header-content');
echo html_writer::link(
    new moodle_url('/local/studentworks/index.php'),
    '<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . get_string('back', 'local_studentworks'),
    ['class' => 'sw-btn sw-btn-ghost sw-btn-sm', 'style' => 'margin-bottom: var(--sw-space-2);']
);
echo html_writer::tag('h1', get_string('uploadwork', 'local_studentworks'), ['class' => 'sw-title', 'style' => 'font-size: 1.75rem;']);
echo html_writer::end_div();
echo html_writer::end_div();

// Form card.
echo html_writer::start_div('sw-info-card', ['style' => 'max-width: 800px;']);
$mform->display();
echo html_writer::end_div();

echo html_writer::end_div();

echo $OUTPUT->footer();
