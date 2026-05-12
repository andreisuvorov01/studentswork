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
 * Class teacher_dashboard for rendering teacher dashboard.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/studentworks/lib.php');

/**
 * Teacher dashboard renderable class.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacher_dashboard implements \renderable, \templatable {

    /** @var array Records of student works */
    private $records;

    /** @var array Filter options */
    private $filters;

    /** @var string Current search query */
    private $searchquery;

    /** @var int Current page number */
    private $page;

    /** @var int Number of items per page */
    private $perpage;

    /** @var \context_system System context */
    private $context;

    /** @var int Total count of records */
    private $totalcount;

    /** @var bool Use work manager for status handling */
    private $usestatusfield;

    /** @var int|null Total count of all records (for pagination) */
    private $totalrecords;

    /**
     * Constructor.
     *
     * @param array $records Student works records
     * @param array $filters Filter options
     * @param string $searchquery Search query
     * @param int $page Current page number
     * @param int $perpage Items per page
     * @param bool $usestatusfield Whether to use status field from database
     * @param int|null $totalrecords Total count of all records (for pagination)
     */
    public function __construct(
        array $records,
        array $filters = [],
        string $searchquery = '',
        int $page = 0,
        int $perpage = 20,
        bool $usestatusfield = true,
        ?int $totalrecords = null
    ) {
        $this->records = $records;
        $this->filters = $filters;
        $this->searchquery = $searchquery;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->context = \context_system::instance();
        $this->totalcount = count($records);
        $this->usestatusfield = $usestatusfield;
        $this->totalrecords = $totalrecords ?? count($records);
    }

    /**
     * Export data for template.
     *
     * @param \renderer_base $output Renderer
     * @return array Template data
     */
    public function export_for_template(\renderer_base $output): array {
        $worktypes = \local_studentworks_get_worktypes();
        $data = [];
        $stats = [
            'total' => 0,
            'reviewed' => 0,
            'pending' => 0,
            'rejected' => 0,
            'students' => []
        ];

        // Calculate stats from all records (before pagination).
        foreach ($this->records as $rec) {
            $stats['students'][$rec->userid] = true;

            $stats['total']++;
            if ($this->usestatusfield && isset($rec->status)) {
                // Use database status field.
                if ($rec->status === \local_studentworks\manager\work_manager::STATUS_REVIEWED) {
                    $stats['reviewed']++;
                } else if ($rec->status === \local_studentworks\manager\work_manager::STATUS_REJECTED) {
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

                if (!empty($reviewfiles)) {
                    $stats['reviewed']++;
                } else {
                    $stats['pending']++;
                }
            }
        }

        $stats['students'] = count($stats['students']);

        // Apply pagination.
        $pagedrecords = array_slice($this->records, $this->page * $this->perpage, $this->perpage, true);

        $index = 0;
        foreach ($pagedrecords as $rec) {
            // Initialize hasreview to false by default
            $hasreview = false;
            
            // Determine status from database field or fallback to file-based detection.
            if ($this->usestatusfield && isset($rec->status)) {
                $status = get_string('status_' . $rec->status, 'local_studentworks');
                $statusclass = $this->get_status_class($rec->status);
                $statuscode = $rec->status;
                // Work has review if status is reviewed or under_review (has review file attached).
                $hasreview = in_array($rec->status, [
                    \local_studentworks\manager\work_manager::STATUS_REVIEWED,
                    \local_studentworks\manager\work_manager::STATUS_UNDER_REVIEW
                ]);
            } else {
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
                    $statuscode = 'reviewed';
                } else {
                    $status = get_string('submitted', 'local_studentworks');
                    $statusclass = 'warning';
                    $statuscode = 'submitted';
                }
            }

            $viewurl = new \moodle_url('/local/studentworks/view.php', ['id' => $rec->id]);
            $reviewurl = new \moodle_url('/local/studentworks/upload.php', ['id' => $rec->id, 'review' => 1]);

            // Build status options for this specific work
            $statusoptions = [];
            $allstatuses = \local_studentworks\manager\work_manager::get_statuses();
            foreach ($allstatuses as $code => $label) {
                $statusoptions[] = [
                    'value' => $code,
                    'label' => $label,
                    'selected' => $statuscode === $code,
                ];
            }

            $data[] = [
                'id' => $rec->id,
                'worktype' => $worktypes[$rec->worktype] ?? $rec->worktype,
                'topic' => format_text($rec->topic, FORMAT_PLAIN, ['context' => $this->context]),
                'discipline' => format_text($rec->discipline, FORMAT_PLAIN, ['context' => $this->context]),
                'author' => fullname($rec),
                'author_initials' => $this->get_initials($rec->firstname, $rec->lastname),
                'author_email' => $rec->email,
                'viewurl' => $viewurl->out(false),
                'reviewurl' => $reviewurl->out(false),
                'timecreated' => $this->format_date($rec->timecreated),
                'timecreated_iso' => date('c', $rec->timecreated),
                'status' => $status,
                'statusclass' => $statusclass,
                'statuscode' => $statuscode,
                'canreview' => true,
                'hasreview' => (bool)$hasreview,
                'canupdatestatus' => $this->usestatusfield,
                'statuses' => $statusoptions,
                'delay' => $index * 0.05,
            ];
            $index++;
        }

        return [
            'works' => $data,
            'hasworks' => !empty($data),
            'stats' => $stats,
            'filters' => $this->get_filter_options($worktypes),
            'searchquery' => s($this->searchquery),
            'datefrom' => $this->filters['date_from'] ?? '',
            'dateto' => $this->filters['date_to'] ?? '',
            'pagination' => $this->get_pagination_data()
        ];
    }

    /**
     * Get filter options for template.
     *
     * @param array $worktypes Available work types
     * @return array Filter options
     */
    private function get_filter_options(array $worktypes): array {
        $types = [];
        foreach ($worktypes as $key => $label) {
            $types[] = [
                'value' => $key,
                'label' => $label,
                'selected' => ($this->filters['type'] ?? '') === $key
            ];
        }

        $statuses = [];
        $allstatuses = \local_studentworks\manager\work_manager::get_statuses();
        foreach ($allstatuses as $code => $label) {
            $statuses[] = [
                'value' => $code,
                'label' => $label,
                'selected' => ($this->filters['status'] ?? '') === $code,
            ];
        }

        return [
            'types' => $types,
            'statuses' => $statuses
        ];
    }

    /**
     * Get pagination data.
     *
     * @return array Pagination data
     */
    private function get_pagination_data(): ?array {
        $totalpages = ceil($this->totalrecords / $this->perpage);

        if ($totalpages <= 1) {
            return null;
        }

        $pages = [];
        for ($i = 0; $i < $totalpages; $i++) {
            $url = new \moodle_url('/local/studentworks/teacher.php', [
                'page' => $i,
                'type' => $this->filters['type'] ?? '',
                'status' => $this->filters['status'] ?? '',
                'date_from' => $this->filters['date_from'] ?? '',
                'date_to' => $this->filters['date_to'] ?? '',
                'search' => $this->searchquery,
            ]);
            $pages[] = [
                'number' => $i + 1,
                'url' => $url->out(false),
                'current' => $i === $this->page,
            ];
        }

        $prevurl = new \moodle_url('/local/studentworks/teacher.php', [
            'page' => max(0, $this->page - 1),
            'type' => $this->filters['type'] ?? '',
            'status' => $this->filters['status'] ?? '',
            'date_from' => $this->filters['date_from'] ?? '',
            'date_to' => $this->filters['date_to'] ?? '',
            'search' => $this->searchquery,
        ]);
        $nexturl = new \moodle_url('/local/studentworks/teacher.php', [
            'page' => min($totalpages - 1, $this->page + 1),
            'type' => $this->filters['type'] ?? '',
            'status' => $this->filters['status'] ?? '',
            'date_from' => $this->filters['date_from'] ?? '',
            'date_to' => $this->filters['date_to'] ?? '',
            'search' => $this->searchquery,
        ]);

        return [
            'pages' => $pages,
            'hasprev' => $this->page > 0,
            'hasnext' => $this->page < $totalpages - 1,
            'prevurl' => $prevurl->out(false),
            'nexturl' => $nexturl->out(false),
            'prevpage' => max(0, $this->page - 1),
            'nextpage' => min($totalpages - 1, $this->page + 1),
        ];
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
