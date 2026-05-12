<?php
namespace local_studentworks\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

/**
 * Form for teacher review upload.
 */
class review_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $workid = $this->_customdata['workid'] ?? 0;
        $mform->addElement('hidden', 'workid', $workid);
        $mform->setType('workid', PARAM_INT);

        $mform->addElement('filemanager', 'reviewfile',
            get_string('reviewfile', 'local_studentworks'), null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.pdf']]);
        $mform->addRule('reviewfile', get_string('required'), 'required', null, 'client');

        // Add grade field (2-5).
        $gradeoptions = [
            '' => get_string('nograde', 'local_studentworks'),
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5'
        ];
        $mform->addElement('select', 'grade', get_string('grade', 'local_studentworks'), $gradeoptions);
        $mform->setType('grade', PARAM_INT);
        $mform->setDefault('grade', '');

        $this->add_action_buttons(true, get_string('uploadreview', 'local_studentworks'));
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

        // Проверка расширения файла.
        $draftitemid = $data['reviewfile'];
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
                    $errors['reviewfile'] = get_string('err_invalidfiletype', 'local_studentworks', 'PDF');
                    break;
                }
            }
        }

        return $errors;
    }
}
