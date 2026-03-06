<?php
namespace local_studentworks\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/studentworks/lib.php');

/**
 * Form for student work upload.
 */
class studentwork_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $types = \local_studentworks_get_worktypes();

        $mform->addElement('select', 'worktype', get_string('worktype', 'local_studentworks'), $types);
        $mform->addRule('worktype', null, 'required');

        $mform->addElement('text', 'topic', get_string('topic', 'local_studentworks'));
        $mform->setType('topic', PARAM_TEXT);
        $mform->addRule('topic', get_string('required'), 'required', null, 'client');
        $mform->addRule('topic', get_string('minimumchars', 'local_studentworks', 10), 'minlength', 10, 'client');

        // Поле дисциплины - выпадающий список курсов студента.
        $courses = $this->get_user_courses();
        $mform->addElement('select', 'discipline', get_string('discipline', 'local_studentworks'), $courses);
        $mform->addRule('discipline', get_string('required'), 'required', null, 'client');

        $mform->addElement('filemanager', 'workfile',
            get_string('studentfile', 'local_studentworks'), null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.pdf']]);
        $mform->addRule('workfile', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('uploadwork', 'local_studentworks'));
    }

    /**
     * Form validation.
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array Errors array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (strlen(trim($data['topic'])) < 10) {
            $errors['topic'] = get_string('err_topictoolshort', 'local_studentworks');
        }

        if (strlen(trim($data['discipline'])) < 3) {
            $errors['discipline'] = get_string('err_disciplinetoolshort', 'local_studentworks');
        }

        // Проверка расширения файла.
        $draftitemid = $data['workfile'];
        if ($draftitemid) {
            $fs = get_file_storage();
            $draftfiles = $fs->get_area_files(
                \context_user::instance($GLOBALS['USER']->id)->id,
                'user',
                'draft',
                $draftitemid,
                'filename',
                false
            );
            foreach ($draftfiles as $file) {
                $filename = $file->get_filename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($extension !== 'pdf') {
                    $errors['workfile'] = get_string('err_invalidfiletype', 'local_studentworks', 'PDF');
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Get list of user's enrolled courses for discipline selection.
     *
     * @return array Array of course id => course fullname
     */
    private function get_user_courses(): array {
        global $DB, $USER;

        $courses = [];

        // Get all courses where user is enrolled.
        $sql = "SELECT c.id, c.fullname, c.shortname
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE ue.userid = :userid
                AND c.visible = 1
                ORDER BY c.fullname ASC";

        $records = $DB->get_records_sql($sql, ['userid' => $USER->id]);

        foreach ($records as $course) {
            $courses[$course->fullname] = $course->fullname;
        }

        // If no courses found, add a default option.
        if (empty($courses)) {
            $courses[''] = get_string('nocourses', 'local_studentworks');
        }

        return $courses;
    }
}
