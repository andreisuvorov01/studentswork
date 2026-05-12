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
 * Work manager class for centralized work operations.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class work_manager
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class work_manager {
    /** @var string Status: work submitted */
    const STATUS_SUBMITTED = 'submitted';

    /** @var string Status: work under review */
    const STATUS_UNDER_REVIEW = 'under_review';

    /** @var string Status: work reviewed */
    const STATUS_REVIEWED = 'reviewed';

    /** @var string Status: work rejected */
    const STATUS_REJECTED = 'rejected';

    /**
     * Get list of available statuses.
     *
     * @return array Array of status codes and their labels
     */
    public static function get_statuses(): array {
        return [
            self::STATUS_SUBMITTED => get_string('status_submitted', 'local_studentworks'),
            self::STATUS_UNDER_REVIEW => get_string('status_under_review', 'local_studentworks'),
            self::STATUS_REVIEWED => get_string('status_reviewed', 'local_studentworks'),
            self::STATUS_REJECTED => get_string('status_rejected', 'local_studentworks'),
        ];
    }

    /**
     * Get list of works with pagination and filters.
     *
     * @param array $filters Filters (userid, worktype, status, search)
     * @param int $page Page number (0-based)
     * @param int $perpage Items per page
     * @return array Array with records, total, pages, page
     */
    public static function get_works(array $filters = [], int $page = 0, int $perpage = 20): array {
        global $DB;

        error_log('[StudentWorks Debug] work_manager::get_works() called with filters: ' . json_encode($filters));

        $where = [];
        $params = [];

        if (!empty($filters['userid'])) {
            $where[] = 'sw.userid = :userid';
            $params['userid'] = $filters['userid'];
        }

        if (!empty($filters['worktype'])) {
            $where[] = 'sw.worktype = :worktype';
            $params['worktype'] = $filters['worktype'];
        }

        if (!empty($filters['type'])) {
            $where[] = 'sw.worktype = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'sw.status = :status';
            $params['status'] = $filters['status'];
        }

        // Date range filter - from date (inclusive).
        if (!empty($filters['date_from'])) {
            $where[] = 'sw.timecreated >= :date_from';
            $params['date_from'] = strtotime($filters['date_from'] . ' 00:00:00');
            error_log('[StudentWorks Debug] Date from filter: ' . $filters['date_from'] . ' -> timestamp: ' . $params['date_from']);
        }

        // Date range filter - to date (inclusive).
        if (!empty($filters['date_to'])) {
            $where[] = 'sw.timecreated <= :date_to';
            $params['date_to'] = strtotime($filters['date_to'] . ' 23:59:59');
            error_log('[StudentWorks Debug] Date to filter: ' . $filters['date_to'] . ' -> timestamp: ' . $params['date_to']);
        }

        if (!empty($filters['search'])) {
            $where[] = '(LOWER(sw.topic) LIKE :search OR LOWER(sw.discipline) LIKE :search OR ' .
                       'LOWER(u.firstname) LIKE :search OR LOWER(u.lastname) LIKE :search OR ' .
                       'LOWER(u.email) LIKE :search)';
            $params['search'] = '%' . strtolower($filters['search']) . '%';
        }

        $sql = "SELECT sw.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                        u.middlename, u.alternatename, u.email
                FROM {local_studentworks} sw
                JOIN {user} u ON u.id = sw.userid";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY sw.timecreated DESC';

        error_log('[StudentWorks Debug] SQL WHERE clauses: ' . json_encode($where));
        error_log('[StudentWorks Debug] SQL params: ' . json_encode($params));

        $countsql = "SELECT COUNT(*) FROM ($sql) t";
        $total = $DB->count_records_sql($countsql, $params);
        $records = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

        error_log('[StudentWorks Debug] Query result - total: ' . $total . ', records: ' . count($records));

        return [
            'records' => $records,
            'total' => $total,
            'pages' => ceil($total / $perpage),
            'page' => $page,
        ];
    }

    /**
     * Update work status.
     *
     * @param int $workid Work ID
     * @param string $status New status
     * @param string $message Optional message
     * @return bool Success
     */
    public static function update_status(int $workid, string $status, string $message = ''): bool {
        global $DB, $USER;

        error_log('[StudentWorks Debug] update_status called with workid=' . $workid . ', status=' . $status);

        $validstatuses = array_keys(self::get_statuses());
        error_log('[StudentWorks Debug] Valid statuses: ' . json_encode($validstatuses));
        
        if (!in_array($status, $validstatuses)) {
            error_log('[StudentWorks Debug] Status "' . $status . '" not in valid statuses list');
            return false;
        }

        $work = $DB->get_record('local_studentworks', ['id' => $workid]);
        if (!$work) {
            error_log('[StudentWorks Debug] Work with id=' . $workid . ' not found');
            return false;
        }

        $oldstatus = $work->status;
        $work->status = $status;
        $work->timemodified = time();

        error_log('[StudentWorks Debug] Updating work: old status=' . $oldstatus . ', new status=' . $status);
        $result = $DB->update_record('local_studentworks', $work);
        error_log('[StudentWorks Debug] Update result: ' . ($result ? 'true' : 'false'));

        // Send notification to student.
        if ($result && $oldstatus !== $status) {
            self::notify_status_change($work, $status, $message);
        }

        return $result;
    }

    /**
     * Get statistics.
     *
     * @param int|null $userid User ID for user-specific stats, null for all
     * @return object Statistics object with total, reviewed, pending
     */
    public static function get_stats(?int $userid = null): object {
        global $DB;

        $params = [];
        $where = '';

        if ($userid) {
            $where = 'WHERE userid = :userid';
            $params['userid'] = $userid;
        }

        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = :reviewed THEN 1 ELSE 0 END) as reviewed,
                    SUM(CASE WHEN status IN (:submitted, :under_review) THEN 1 ELSE 0 END) as pending
                FROM {local_studentworks}
                $where";

        $params['reviewed'] = self::STATUS_REVIEWED;
        $params['submitted'] = self::STATUS_SUBMITTED;
        $params['under_review'] = self::STATUS_UNDER_REVIEW;

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get unique students count.
     *
     * @return int Number of unique students
     */
    public static function get_students_count(): int {
        global $DB;
        return $DB->count_records_select('local_studentworks', '', null, 'COUNT(DISTINCT userid)');
    }

    /**
     * Notify about status change.
     *
     * @param object $work Work record
     * @param string $newstatus New status
     * @param string $message Optional message
     * @return bool Success
     */
    private static function notify_status_change(object $work, string $newstatus, string $message = ''): bool {
        $notifier = new \local_studentworks\notification\notifier();
        return $notifier->notify_status_change($work, $newstatus, $message);
    }

    /**
     * Set default status for new work.
     *
     * @param int $workid Work ID
     * @return bool Success
     */
    public static function set_default_status(int $workid): bool {
        global $DB;

        $work = $DB->get_record('local_studentworks', ['id' => $workid]);
        if (!$work) {
            return false;
        }

        $work->status = self::STATUS_SUBMITTED;
        $work->timemodified = time();

        return $DB->update_record('local_studentworks', $work);
    }
}