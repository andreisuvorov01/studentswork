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

    // Check permissions: teacher can view all, student can view only own
    $isteacher = local_studentworks_is_teacher();
    $isowner = $USER->id == $work->userid;
    $isstudent = local_studentworks_is_student();

    if (!$isteacher && !$isowner) {
        return false;
    }

    if ($isstudent && !$isteacher && !$isowner) {
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

    list($rolesql, $roleparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'r');
    $sql = "SELECT DISTINCT ra.id
            FROM {role_assignments} ra
            JOIN {role} r ON r.id = ra.roleid
            JOIN {context} ctx ON ctx.id = ra.contextid
            WHERE ra.userid = :userid
            AND r.shortname $rolesql
            AND ctx.contextlevel = :contextlevel";

    $params = array_merge(['userid' => $userid, 'contextlevel' => CONTEXT_COURSE], $roleparams);

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Check if user is a teacher in any course.
 *
 * @param int $userid User ID (default: current user)
 * @return bool True if user is a teacher
 */
function local_studentworks_is_teacher(int $userid = 0): bool {
    global $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    
    // Check if admin
    if (is_siteadmin($userid)) {
        return true;
    }
    
    return local_studentworks_has_course_role($userid, ['teacher', 'editingteacher', 'manager']);
}

/**
 * Check if user is a student in any course.
 *
 * @param int $userid User ID (default: current user)
 * @return bool True if user is a student
 */
function local_studentworks_is_student(int $userid = 0): bool {
    global $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    
    return local_studentworks_has_course_role($userid, ['student']);
}

/**
 * Sets up a standard page for the Student Works plugin.
 *
 * This reusable function handles common page setup tasks including:
 * - URL parameter filtering and validation
 * - Page URL, title, heading, and layout configuration
 * - Body class management
 * - Capability checking
 * - Guest user handling
 *
 * @param string $page The page path (e.g., '/local/studentworks/teacher.php')
 * @param array $urlparams Array of URL parameters to include in the page URL
 * @param string $pagelayout The page layout (default: 'standard')
 * @param string $title The page title
 * @param string $heading The page heading
 * @param array $options Additional options:
 *                       - capabilities: array of required capability names (e.g., ['local/studentworks:viewall'])
 *                       - anycapability: bool - if true, user needs ANY of the capabilities; if false, user needs ALL (default: false)
 *                       - requirelogin: bool - whether login is required (default: true)
 *                       - allowguest: bool - whether to allow guest access (default: false)
 *                       - bodyclasses: array - additional CSS classes for body
 *                       - context: context - the page context (default: CONTEXT_SYSTEM)
 *                       - forcetheme: string|null - force a specific theme
 *                       - hideblocks: bool - whether to hide blocks (default: false)
 *                       - paramdefinitions: array - custom parameter definitions for filtering
 *                         Each definition is ['name' => paramname, 'type' => PARAM_*, 'required' => bool, 'default' => mixed]
 * @return array Returns array of filtered/extracted parameters for further use
 * @throws moodle_exception If required capabilities are not met or guest access is denied
 */
function local_studentworks_page_setup(
    string $page,
    array $urlparams = [],
    string $pagelayout = 'standard',
    string $title = '',
    string $heading = '',
    array $options = []
): array {
    global $PAGE, $CFG, $USER, $DB;

    // Set default options.
    $capabilities = $options['capabilities'] ?? [];
    $requirelogin = $options['requirelogin'] ?? true;
    $allowguest = $options['allowguest'] ?? false;
    $bodyclasses = $options['bodyclasses'] ?? [];
    $context = $options['context'] ?? \context_system::instance();
    $forcetheme = $options['forcetheme'] ?? null;
    $hideblocks = $options['hideblocks'] ?? false;
    $paramdefinitions = $options['paramdefinitions'] ?? [];

    // Handle require_login.
    if ($requirelogin) {
        require_login();
    }

    // Check if user is guest.
    $isguest = isguestuser();

    // Handle guest access.
    if ($isguest && !$allowguest) {
        // Redirect to login page with redirect URL.
        $loginurl = new \moodle_url('/login/index.php');
        $redirecturl = new \moodle_url($page, $urlparams);
        \redirect($loginurl, get_string('loginguest'), 0, \core\output\notification::NOTIFY_INFO);
    }

    // Set page context.
    $PAGE->set_context($context);

    // Force theme if specified.
    if ($forcetheme !== null) {
        $PAGE->set_theme($forcetheme);
    }

    // Hide blocks if requested.
    if ($hideblocks) {
        $PAGE->set_blocksheduling(false);
    }

    // Build and set the page URL.
    $url = new \moodle_url($page, $urlparams);
    $PAGE->set_url($url);

    // Set page title.
    if (!empty($title)) {
        $PAGE->set_title($title);
    }

    // Set page heading.
    if (!empty($heading)) {
        $PAGE->set_heading($heading);
    }

    // Set page layout.
    $PAGE->set_pagelayout($pagelayout);

    // Add body classes.
    if (!empty($bodyclasses)) {
        foreach ($bodyclasses as $class) {
            $PAGE->add_body_class($class);
        }
    }

    // Add plugin-specific body class based on page.
    $pagename = basename($page, '.php');
    $PAGE->add_body_class('local-studentworks-page');
    $PAGE->add_body_class('local-studentworks-' . $pagename);

    // Check capabilities if specified.
    if (!empty($capabilities)) {
        // Check if any capability is required (OR logic) or all capabilities are required (AND logic)
        $anyrequired = $options['anycapability'] ?? false;
        
        if ($anyrequired) {
            // At least one capability must be present
            $hasany = false;
            foreach ($capabilities as $capability) {
                if (has_capability($capability, $context)) {
                    $hasany = true;
                    break;
                }
            }
            if (!$hasany) {
                // None of the capabilities were present, throw exception with first capability name
                throw new \moodle_exception('nopermissions', 'error', '', $capabilities[0]);
            }
        } else {
            // All capabilities must be present (AND logic)
            foreach ($capabilities as $capability) {
                if (!has_capability($capability, $context)) {
                    throw new \moodle_exception('nopermissions', 'error', '', $capability);
                }
            }
        }
    }

    // Process parameter definitions and extract filtered values.
    $filteredparams = [];

    if (!empty($paramdefinitions)) {
        foreach ($paramdefinitions as $paramdef) {
            $name = $paramdef['name'];
            $type = $paramdef['type'] ?? PARAM_RAW;
            $required = $paramdef['required'] ?? false;
            $default = $paramdef['default'] ?? null;

            if ($required) {
                // Use required_param for required parameters.
                $value = required_param($name, $type);
            } else {
                // Use optional_param for optional parameters.
                $value = optional_param($name, $default, $type);
            }

            // Additional validation for date parameters
            if (strpos($name, 'date') !== false && !empty($value)) {
                // Validate date format (YYYY-MM-DD)
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    // Reset invalid date to default
                    $value = $default;
                }
            }

            $filteredparams[$name] = $value;
        }
    }

    // Return filtered parameters for further use.
    return $filteredparams;
}
