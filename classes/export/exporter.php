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
 * Export class for student works.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\export;

defined('MOODLE_INTERNAL') || die();

/**
 * Class exporter
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exporter {

    /**
     * Export works to CSV.
     *
     * @param array $works Array of work records
     * @param string $filename Download filename
     * @return void
     */
    public static function export_csv(array $works, string $filename = 'works.csv'): void {
        global $CFG;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8 support.
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers.
        fputcsv($output, [
            get_string('student', 'local_studentworks'),
            get_string('email', 'local_studentworks'),
            get_string('worktype', 'local_studentworks'),
            get_string('discipline', 'local_studentworks'),
            get_string('topic', 'local_studentworks'),
            get_string('status', 'local_studentworks'),
            get_string('date', 'local_studentworks'),
        ]);

        foreach ($works as $work) {
            $statuslabel = get_string('status_' . $work->status, 'local_studentworks');
            $worktypelabel = get_string($work->worktype, 'local_studentworks');

            fputcsv($output, [
                fullname($work),
                $work->email,
                $worktypelabel,
                $work->discipline,
                $work->topic,
                $statuslabel,
                userdate($work->timecreated, get_string('strftimedatetime', 'langconfig')),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export works to Excel format.
     *
     * @param array $works Array of work records
     * @param string $filename Download filename
     * @return void
     */
    public static function export_excel(array $works, string $filename = 'works.xlsx'): void {
        global $CFG;

        // Use core_excel library if available (Moodle 4.1+).
        if (class_exists('\core\excel')) {
            self::export_excel_core($works, $filename);
            return;
        }

        // Fallback to CSV if Excel library is not available.
        self::export_csv($works, str_replace('.xlsx', '.csv', $filename));
    }

    /**
     * Export using core Excel library.
     *
     * @param array $works Array of work records
     * @param string $filename Download filename
     * @return void
     */
    private static function export_excel_core(array $works, string $filename): void {
        $excel = new \core\excel();

        // Add worksheet.
        $sheet = $excel->get_sheet(0);
        $sheet->set_title(get_string('workslist', 'local_studentworks'));

        // Headers.
        $headers = [
            get_string('student', 'local_studentworks'),
            get_string('email', 'local_studentworks'),
            get_string('worktype', 'local_studentworks'),
            get_string('discipline', 'local_studentworks'),
            get_string('topic', 'local_studentworks'),
            get_string('status', 'local_studentworks'),
            get_string('date', 'local_studentworks'),
        ];

        foreach ($headers as $col => $header) {
            $sheet->write_string(0, $col, $header);
            $sheet->set_column_width($col, 20);
        }

        // Data.
        $row = 1;
        foreach ($works as $work) {
            $statuslabel = get_string('status_' . $work->status, 'local_studentworks');
            $worktypelabel = get_string($work->worktype, 'local_studentworks');

            $sheet->write_string($row, 0, fullname($work));
            $sheet->write_string($row, 1, $work->email);
            $sheet->write_string($row, 2, $worktypelabel);
            $sheet->write_string($row, 3, $work->discipline);
            $sheet->write_string($row, 4, $work->topic);
            $sheet->write_string($row, 5, $statuslabel);
            $sheet->write_string($row, 6, userdate($work->timecreated, get_string('strftimedatetime', 'langconfig')));

            $row++;
        }

        $excel->download($filename);
        exit;
    }

    /**
     * Export works to JSON.
     *
     * @param array $works Array of work records
     * @param string $filename Download filename
     * @return void
     */
    public static function export_json(array $works, string $filename = 'works.json'): void {
        $data = [];

        foreach ($works as $work) {
            $data[] = [
                'id' => $work->id,
                'student' => fullname($work),
                'email' => $work->email,
                'worktype' => get_string($work->worktype, 'local_studentworks'),
                'discipline' => $work->discipline,
                'topic' => $work->topic,
                'status' => $work->status,
                'status_label' => get_string('status_' . $work->status, 'local_studentworks'),
                'timecreated' => $work->timecreated,
                'timecreated_formatted' => userdate($work->timecreated),
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}