<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the list of available work types with localized names.
 *
 * @return array Associative array of work type codes and their localized names
 */
function local_studentworks_get_worktypes() {
    return [
        'coursework' => get_string('coursework', 'local_studentworks'),
        'practice' => get_string('practice', 'local_studentworks'),
        'vkr' => get_string('vkr', 'local_studentworks')
    ];
}

function local_studentworks_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'workfile' && $filearea !== 'reviewfile') {
        return false;
    }

    require_login();

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_studentworks', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    $work = $DB->get_record('local_studentworks', ['id' => $itemid]);
    if (!$work) {
        return false;
    }

    // Check permissions: by capability, course role, or ownership.
    $canviewall = has_capability('local/studentworks:viewall', $context) ||
                  local_studentworks_has_teacher_access($USER->id, $context);
    $isowner = $USER->id == $work->userid;
    $hasstudentaccess = has_capability('local/studentworks:viewown', $context) ||
                        local_studentworks_has_student_access($USER->id, $context);

    if (!$canviewall && !$isowner && !$hasstudentaccess) {
        return false;
    }

    // If user only has student access, they can only view their own files.
    if (!$canviewall && $hasstudentaccess && !$isowner) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);
}

/**
 * Check if user has a specific role in any course.
 *
 * @param int $userid User ID
 * @param array $roles Array of role shortnames to check (e.g., ['teacher', 'editingteacher'])
 * @return bool True if user has any of the roles in at least one course
 */
function local_studentworks_has_course_role(int $userid, array $roles): bool {
    global $DB;

    if (empty($roles)) {
        return false;
    }

    // Get role IDs from shortnames.
    list($rolesql, $roleparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'r');
    $sql = "SELECT DISTINCT ra.id
            FROM {role_assignments} ra
            JOIN {role} r ON r.id = ra.roleid
            WHERE ra.userid = :userid
            AND r.shortname $rolesql";

    $params = array_merge(['userid' => $userid], $roleparams);

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Check if user is a teacher in any course.
 *
 * @param int $userid User ID
 * @return bool True if user is a teacher (has teacher or editingteacher role) in any course
 */
function local_studentworks_is_teacher_in_any_course(int $userid): bool {
    return local_studentworks_has_course_role($userid, ['teacher', 'editingteacher', 'manager']);
}

/**
 * Check if user is a student in any course.
 *
 * @param int $userid User ID
 * @return bool True if user has student role in any course
 */
function local_studentworks_is_student_in_any_course(int $userid): bool {
    return local_studentworks_has_course_role($userid, ['student']);
}

/**
 * Check if user has access as teacher (either by capability or course role).
 *
 * @param int $userid User ID
 * @param \context $context System context
 * @return bool True if user has teacher access
 */
function local_studentworks_has_teacher_access(int $userid, \context $context): bool {
    // Check capabilities first (for admins, etc.).
    if (has_capability('local/studentworks:viewall', $context, $userid)) {
        return true;
    }

    // Check review capability (for teachers who can upload reviews).
    if (has_capability('local/studentworks:review', $context, $userid)) {
        return true;
    }

    // Check if user has teacher role in any course.
    return local_studentworks_is_teacher_in_any_course($userid);
}

/**
 * Check if user has access as student (either by capability or course enrollment).
 *
 * @param int $userid User ID
 * @param \context $context System context
 * @return bool True if user has student access
 */
function local_studentworks_has_student_access(int $userid, \context $context): bool {
    // Check capability first.
    if (has_capability('local/studentworks:upload', $context, $userid)) {
        return true;
    }

    // Check if user has student role in any course or is just a regular user.
    return local_studentworks_is_student_in_any_course($userid) ||
           local_studentworks_has_course_role($userid, ['user', 'frontpage']);
}
