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
 * Class work_detail for rendering work detail page.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/studentworks/lib.php');

/**
 * Work detail renderable class.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class work_detail implements \renderable, \templatable {

    /** @var \stdClass Work record */
    private $work;

    /** @var \stdClass Author user record */
    private $author;

    /** @var \stored_file|null Work file */
    private $workfile;

    /** @var \stored_file|null Review file */
    private $reviewfile;

    /** @var bool Whether user can review */
    private $canreview;

    /** @var \moodle_url URL to go back */
    private $backurl;

    /** @var \context_system System context */
    private $context;

    /**
     * Constructor.
     *
     * @param \stdClass $work Work record
     * @param \stdClass $author Author user record
     * @param \stored_file|null $workfile Work file
     * @param \stored_file|null $reviewfile Review file
     * @param bool $canreview Whether user can review
     * @param \moodle_url $backurl URL to go back
     */
    public function __construct(
        \stdClass $work,
        \stdClass $author,
        ?\stored_file $workfile,
        ?\stored_file $reviewfile,
        bool $canreview,
        \moodle_url $backurl
    ) {
        $this->work = $work;
        $this->author = $author;
        $this->workfile = $workfile;
        $this->reviewfile = $reviewfile;
        $this->canreview = $canreview;
        $this->backurl = $backurl;
        $this->context = \context_system::instance();
    }

    /**
     * Export data for template.
     *
     * @param \renderer_base $output Renderer
     * @return array Template data
     */
    public function export_for_template(\renderer_base $output): array {
        $worktypes = \local_studentworks_get_worktypes();

        // Determine status from database field if available, otherwise fallback to file-based detection.
        if (isset($this->work->status)) {
            $statuscode = $this->work->status;
            $status = get_string('status_' . $statuscode, 'local_studentworks');
            $statusclass = $this->get_status_class($statuscode);
        } else if ($this->reviewfile) {
            $status = get_string('reviewed', 'local_studentworks');
            $statusclass = 'success';
        } else {
            $status = get_string('submitted', 'local_studentworks');
            $statusclass = 'warning';
        }

        $reviewurl = new \moodle_url('/local/studentworks/upload.php', [
            'id' => $this->work->id,
            'review' => 1
        ]);

        // Check if grade is set.
        $hasgrade = isset($this->work->grade) && $this->work->grade !== null;

        return [
            'work' => [
                'id' => $this->work->id,
                'worktype' => $worktypes[$this->work->worktype] ?? $this->work->worktype,
                'topic' => format_text($this->work->topic, FORMAT_PLAIN, ['context' => $this->context]),
                'discipline' => format_text($this->work->discipline, FORMAT_PLAIN, ['context' => $this->context]),
                'timecreated' => $this->format_date($this->work->timecreated),
                'timecreated_iso' => date('c', $this->work->timecreated),
                'timemodified' => !empty($this->work->timemodified) ? $this->format_date($this->work->timemodified) : null,
                'timemodified_iso' => !empty($this->work->timemodified) ? date('c', $this->work->timemodified) : null,
                'status' => $status,
                'statusclass' => $statusclass,
                'hasgrade' => $hasgrade,
                'grade' => $hasgrade ? $this->work->grade : null
            ],
            'author' => [
                'name' => fullname($this->author),
                'email' => $this->author->email,
                'initials' => $this->get_initials($this->author->firstname, $this->author->lastname)
            ],
            'files' => [
                'work' => $this->get_file_data($this->workfile, 'workfile'),
                'review' => $this->get_file_data($this->reviewfile, 'reviewfile')
            ],
            'canreview' => $this->canreview,
            'backurl' => $this->backurl->out(false),
            'reviewurl' => $reviewurl->out(false),
            'history' => $this->get_history()
        ];
    }

    /**
     * Get file data for template.
     *
     * @param \stored_file|null $file File object
     * @param string $filearea File area name
     * @return array|null File data
     */
    private function get_file_data(?\stored_file $file, string $filearea): ?array {
        if (!$file) {
            return null;
        }

        $url = \moodle_url::make_pluginfile_url(
            $this->context->id,
            'local_studentworks',
            $filearea,
            $this->work->id,
            '/',
            $file->get_filename()
        );

        return [
            'filename' => $file->get_filename(),
            'filesize' => $this->format_filesize($file->get_filesize()),
            'downloadurl' => $url->out(false),
            'mimetype' => $file->get_mimetype()
        ];
    }

    /**
     * Get work history.
     *
     * @return array|null History data
     */
    private function get_history(): ?array {
        $history = [];

        // Work submission.
        $history[] = [
            'action' => get_string('worksubmitted', 'local_studentworks'),
            'description' => get_string('workuploadedby', 'local_studentworks', fullname($this->author)),
            'timestamp' => $this->format_date($this->work->timecreated),
            'timestamp_iso' => date('c', $this->work->timecreated),
            'icon' => 'fa-upload',
            'icon_bg' => 'var(--sw-primary-light)',
            'icon_color' => 'var(--sw-primary)'
        ];

        // Review submission (if exists).
        if ($this->reviewfile) {
            $history[] = [
                'action' => get_string('reviewsubmitted', 'local_studentworks'),
                'description' => $this->reviewfile->get_filename(),
                'timestamp' => $this->format_date($this->reviewfile->get_timecreated()),
                'timestamp_iso' => date('c', $this->reviewfile->get_timecreated()),
                'icon' => 'fa-check',
                'icon_bg' => 'var(--sw-success-light)',
                'icon_color' => 'var(--sw-success)'
            ];
        }

        return !empty($history) ? ['history_items' => $history] : null;
    }

    /**
     * Get initials from first and last name.
     *
     * @param string $firstname First name
     * @param string $lastname Last name
     * @return string Initials
     */
    private function get_initials(string $firstname, string $lastname): string {
        $initials = '';
        if (!empty($firstname)) {
            $initials .= mb_strtoupper(mb_substr($firstname, 0, 1));
        }
        if (!empty($lastname)) {
            $initials .= mb_strtoupper(mb_substr($lastname, 0, 1));
        }
        return $initials;
    }

    /**
     * Format file size for display.
     *
     * @param int $size File size in bytes
     * @return string Formatted size
     */
    private function format_filesize(int $size): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitindex = 0;

        while ($size >= 1024 && $unitindex < count($units) - 1) {
            $size /= 1024;
            $unitindex++;
        }

        return round($size, 1) . ' ' . $units[$unitindex];
    }

    /**
     * Format timestamp for display.
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted date
     */
    private function format_date(int $timestamp): string {
        return userdate($timestamp, get_string('strftimedatetime', 'langconfig'));
    }

    /**
     * Get CSS class for status.
     *
     * @param string $status Status code
     * @return string CSS class
     */
    private function get_status_class(string $status): string {
        $classes = [
            'submitted' => 'warning',
            'under_review' => 'info',
            'reviewed' => 'success',
            'rejected' => 'danger',
        ];
        return $classes[$status] ?? 'secondary';
    }
}
