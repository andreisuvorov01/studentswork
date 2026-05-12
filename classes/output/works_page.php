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
 * Class works_page for rendering student works list.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/studentworks/lib.php');

/**
 * Works page renderable class.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class works_page implements \renderable, \templatable {

    /** @var array Records of student works */
    private $records;

    /** @var \moodle_url URL for upload page */
    private $uploadurl;

    /** @var \context_system System context */
    private $context;

    /**
     * Constructor.
     *
     * @param array $records Student works records
     * @param \moodle_url $uploadurl Upload page URL
     */
    public function __construct($records, \moodle_url $uploadurl) {
        $this->records = $records;
        $this->uploadurl = $uploadurl;
        $this->context = \context_system::instance();
    }

    /**
     * Export data for template.
     *
     * @param \renderer_base $output Renderer
     * @return array Template data
     */
    public function export_for_template(\renderer_base $output): array {
        global $USER;

        $worktypes = \local_studentworks_get_worktypes();
        $data = [];
        $stats = [
            'total' => 0,
            'reviewed' => 0,
            'pending' => 0,
            'rejected' => 0
        ];

        foreach ($this->records as $rec) {
            if ($rec->userid != $USER->id) {
                continue;
            }

            $stats['total']++;

            // Determine status from database field if available, otherwise fallback to file-based detection.
            if (isset($rec->status)) {
                $statuscode = $rec->status;
                $status = get_string('status_' . $statuscode, 'local_studentworks');
                $statusclass = $this->get_status_class($statuscode);
                $hasreview = in_array($statuscode, [
                    \local_studentworks\manager\work_manager::STATUS_REVIEWED,
                    \local_studentworks\manager\work_manager::STATUS_UNDER_REVIEW
                ]);
                
                // Update stats based on status
                if ($statuscode === \local_studentworks\manager\work_manager::STATUS_REVIEWED) {
                    $stats['reviewed']++;
                } else if ($statuscode === \local_studentworks\manager\work_manager::STATUS_REJECTED) {
                    $stats['rejected']++;
                } else {
                    $stats['pending']++;
                }
            } else {
                // Fallback to file-based status detection.
                $fs = get_file_storage();
                $reviewfiles = $fs->get_area_files(
                    $this->context->id,
                    'local_studentworks',
                    'reviewfile',
                    $rec->id,
                    'filename',
                    false
                );

                $hasreview = !empty($reviewfiles);
                if ($hasreview) {
                    $status = get_string('reviewed', 'local_studentworks');
                    $statusclass = 'success';
                    $stats['reviewed']++;
                } else {
                    $status = get_string('submitted', 'local_studentworks');
                    $statusclass = 'warning';
                    $stats['pending']++;
                }
            }

            $viewurl = new \moodle_url('/local/studentworks/view.php', ['id' => $rec->id]);

            // Check if grade is set.
            $hasgrade = isset($rec->grade) && $rec->grade !== null;

            $data[] = [
                'id' => $rec->id,
                'worktype' => $worktypes[$rec->worktype] ?? $rec->worktype,
                'topic' => format_text($rec->topic, FORMAT_PLAIN, ['context' => $this->context]),
                'discipline' => format_text($rec->discipline, FORMAT_PLAIN, ['context' => $this->context]),
                'author' => fullname($rec),
                'viewurl' => $viewurl->out(false),
                'timecreated' => $this->format_date($rec->timecreated),
                'timecreated_iso' => date('c', $rec->timecreated),
                'status' => $status,
                'statusclass' => $statusclass,
                'hasreview' => $hasreview,
                'hasgrade' => $hasgrade,
                'grade' => $hasgrade ? $rec->grade : null
            ];
        }

        return [
            'works' => $data,
            'uploadurl' => $this->uploadurl->out(false),
            'hasworks' => !empty($data),
            'stats' => $stats
        ];
    }

    /**
     * Format timestamp for display.
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted date
     */
    private function format_date(int $timestamp): string {
        return userdate($timestamp, get_string('strftimedate', 'langconfig'));
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
