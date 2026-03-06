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
 * Student Works renderer class.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_studentworks\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for Student Works plugin.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render the works page for students.
     *
     * @param works_page $page The works page renderable
     * @return string HTML output
     */
    public function render_works_page(works_page $page): string {
        return $this->render_from_template(
            'local_studentworks/works_page',
            $page->export_for_template($this)
        );
    }

    /**
     * Render the teacher dashboard.
     *
     * @param teacher_dashboard $dashboard The teacher dashboard renderable
     * @return string HTML output
     */
    public function render_teacher_dashboard(teacher_dashboard $dashboard): string {
        return $this->render_from_template(
            'local_studentworks/teacher_dashboard',
            $dashboard->export_for_template($this)
        );
    }

    /**
     * Render the work detail page.
     *
     * @param work_detail $detail The work detail renderable
     * @return string HTML output
     */
    public function render_work_detail(work_detail $detail): string {
        return $this->render_from_template(
            'local_studentworks/work_detail',
            $detail->export_for_template($this)
        );
    }

    /**
     * Render the upload page.
     *
     * @param \moodleform $form The upload form
     * @param string $title Page title
     * @return string HTML output
     */
    public function render_upload_form(\moodleform $form, string $title): string {
        $data = [
            'title' => $title,
            'form' => $form->render()
        ];
        return $this->render_from_template('local_studentworks/upload_form', $data);
    }

    /**
     * Render a toast notification.
     *
     * @param string $message The message to display
     * @param string $type The type of notification (success, warning, danger, info)
     * @param string $title Optional title
     * @return string HTML output
     */
    public function render_toast(string $message, string $type = 'info', string $title = ''): string {
        $data = [
            'message' => $message,
            'type' => $type,
            'title' => $title,
            'has_title' => !empty($title)
        ];
        return $this->render_from_template('local_studentworks/toast', $data);
    }
}
