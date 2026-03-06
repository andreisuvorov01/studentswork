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
 * Task for cleaning up old files.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class cleanup_old_files
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_old_files extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_cleanup_old_files', 'local_studentworks');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        mtrace('Starting cleanup of old student works...');

        // Define retention period (365 days).
        $retentionperiod = 365 * 24 * 60 * 60;
        $cutofftime = time() - $retentionperiod;

        // Get old rejected works.
        $oldworks = $DB->get_records_select(
            'local_studentworks',
            'status = :status AND timemodified < :cutoff',
            [
                'status' => \local_studentworks\manager\work_manager::STATUS_REJECTED,
                'cutoff' => $cutofftime,
            ]
        );

        $deletedcount = 0;
        $fs = get_file_storage();
        $context = \context_system::instance();

        foreach ($oldworks as $work) {
            // Delete associated files.
            $fs->delete_area_files(
                $context->id,
                'local_studentworks',
                'studentfile',
                $work->id
            );

            $fs->delete_area_files(
                $context->id,
                'local_studentworks',
                'reviewfile',
                $work->id
            );

            // Delete the record.
            $DB->delete_records('local_studentworks', ['id' => $work->id]);
            $deletedcount++;

            mtrace("Deleted old rejected work: ID {$work->id}");
        }

        mtrace("Cleanup completed. Deleted {$deletedcount} old works.");
    }
}