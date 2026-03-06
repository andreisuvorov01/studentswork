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

// Check upload capability for new works.
if (!$id && !$review) {
    if (!has_capability('local/studentworks:upload', $context) &&
        !\local_studentworks_has_student_access($USER->id, $context)) {
        throw new moodle_exception('nopermissions', 'error', '', get_string('studentworks:upload', 'local_studentworks'));
    }
} else if ($review) {
    if (!has_capability('local/studentworks:review', $context) &&
        !\local_studentworks_has_teacher_access($USER->id, $context)) {
        throw new moodle_exception('nopermissions', 'error', '', get_string('studentworks:review', 'local_studentworks'));
    }
}

$PAGE->set_context($context);
$PAGE->set_url('/local/studentworks/upload.php', ['id' => $id, 'review' => $review]);
$PAGE->set_pagelayout('standard');

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Handle review upload.
if ($review && $id) {
    $work = $DB->get_record('local_studentworks', ['id' => $id], '*', MUST_EXIST);
    $PAGE->set_title(get_string('uploadreview', 'local_studentworks'));
    $PAGE->set_heading(get_string('uploadreview', 'local_studentworks'));

    $mform = new \local_studentworks\form\review_form(null, ['workid' => $id]);

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/studentworks/view.php', ['id' => $id]));
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

$mform = new \local_studentworks\form\studentwork_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/studentworks/index.php'));
}

if ($data = $mform->get_data()) {
    // Check for duplicates.
    $existing = $DB->get_record_sql(
        "SELECT * FROM {local_studentworks}
         WHERE userid = :userid
         AND LOWER(topic) = LOWER(:topic)
         AND LOWER(discipline) = LOWER(:discipline)
         AND worktype = :worktype",
        [
            'userid' => $USER->id,
            'topic' => trim($data->topic),
            'discipline' => trim($data->discipline),
            'worktype' => $data->worktype
        ]
    );

    if ($existing) {
        throw new moodle_exception('duplicatework', 'local_studentworks');
    }

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->worktype = $data->worktype;
    $record->topic = trim($data->topic);
    $record->discipline = trim($data->discipline);
    $record->timecreated = time();
    $record->timemodified = time();
    $record->status = \local_studentworks\manager\work_manager::STATUS_SUBMITTED;

    $id = $DB->insert_record('local_studentworks', $record);

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
            throw new moodle_exception('fileuploaderror', 'local_studentworks');
        }
    } catch (Exception $e) {
        // Delete record on file upload error.
        $DB->delete_records('local_studentworks', ['id' => $id]);
        throw new moodle_exception('fileuploaderror', 'local_studentworks');
    }

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
