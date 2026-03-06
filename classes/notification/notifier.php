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
 * Notification class for student works.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Class notifier
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifier {

    /**
     * Notify student about status change.
     *
     * @param object $work Work record
     * @param string $newstatus New status code
     * @param string $message Optional message
     * @return bool Success
     */
    public function notify_status_change(object $work, string $newstatus, string $message = ''): bool {
        global $DB;

        $user = $DB->get_record('user', ['id' => $work->userid]);
        if (!$user) {
            return false;
        }

        $statuslabel = get_string('status_' . $newstatus, 'local_studentworks');

        $subject = get_string('status_change_subject', 'local_studentworks');
        $body = get_string('status_change_body', 'local_studentworks', [
            'topic' => $work->topic,
            'status' => $statuslabel,
            'message' => $message,
        ]);

        // Create message object for Moodle 5.x API.
        $msg = new \core\message\message();
        $msg->component = 'local_studentworks';
        $msg->name = 'status_change';
        $msg->userfrom = \core_user::get_noreply_user();
        $msg->userto = $user;
        $msg->subject = $subject;
        $msg->fullmessage = $body;
        $msg->fullmessageformat = FORMAT_PLAIN;
        $msg->fullmessagehtml = nl2br($body);
        $msg->smallmessage = $subject;
        $msg->notification = 1;

        return message_send($msg);
    }

    /**
     * Notify teachers about new work.
     *
     * @param object $work Work record
     * @return int Number of notifications sent
     */
    public function notify_new_work(object $work): int {
        global $DB;

        $context = \context_system::instance();
        $teachers = get_users_by_capability($context, 'local/studentworks:viewall');

        if (empty($teachers)) {
            return 0;
        }

        $student = $DB->get_record('user', ['id' => $work->userid]);
        if (!$student) {
            return 0;
        }

        $subject = get_string('new_work_subject', 'local_studentworks');
        $body = get_string('new_work_body', 'local_studentworks', [
            'student' => fullname($student),
            'topic' => $work->topic,
            'discipline' => $work->discipline,
        ]);

        $count = 0;
        foreach ($teachers as $teacher) {
            // Create message object for Moodle 5.x API.
            $msg = new \core\message\message();
            $msg->component = 'local_studentworks';
            $msg->name = 'new_work';
            $msg->userfrom = $student;
            $msg->userto = $teacher;
            $msg->subject = $subject;
            $msg->fullmessage = $body;
            $msg->fullmessageformat = FORMAT_PLAIN;
            $msg->fullmessagehtml = nl2br($body);
            $msg->smallmessage = $subject;
            $msg->notification = 1;

            if (message_send($msg)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send bulk notification to multiple users.
     *
     * @param array $users Array of user objects
     * @param string $subject Subject
     * @param string $body Body
     * @param int $from User ID from whom to send
     * @return int Number of notifications sent
     */
    public function send_bulk_notification(array $users, string $subject, string $body, int $from = 0): int {
        $fromuser = $from ? \core_user::get_user($from) : \core_user::get_noreply_user();

        $count = 0;
        foreach ($users as $user) {
            // Create message object for Moodle 5.x API.
            $msg = new \core\message\message();
            $msg->component = 'local_studentworks';
            $msg->name = 'status_change'; // Using existing message type.
            $msg->userfrom = $fromuser;
            $msg->userto = $user;
            $msg->subject = $subject;
            $msg->fullmessage = $body;
            $msg->fullmessageformat = FORMAT_PLAIN;
            $msg->fullmessagehtml = nl2br($body);
            $msg->smallmessage = $subject;
            $msg->notification = 1;

            if (message_send($msg)) {
                $count++;
            }
        }

        return $count;
    }
}
