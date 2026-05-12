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
 * Student Works - Teacher Dashboard
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/studentworks/lib.php');

// Debug: log all requests to this file.
error_log('[StudentWorks Debug] teacher.php accessed. Action: ' . optional_param('action', 'none', PARAM_ALPHANUMEXT));

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

// Check access: teacher role in any course
if (!local_studentworks_is_teacher()) {
    throw new moodle_exception('nopermissions', 'error', '', 'Access denied - teacher role required');
}

// Get parameters.
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$filtertype = optional_param('type', '', PARAM_ALPHA);
$filterstatus = optional_param('status', '', PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);
$datefrom = optional_param('date_from', '', PARAM_TEXT);
$dateto = optional_param('date_to', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$export = optional_param('export', '', PARAM_ALPHA);

// Handle AJAX status update.
if ($action === 'update_status') {
    error_log('[StudentWorks Debug] update_status action detected');
    error_log('[StudentWorks Debug] POST data: ' . print_r($_POST, true));
    error_log('[StudentWorks Debug] Sesskey from request: ' . optional_param('sesskey', '', PARAM_RAW));
    error_log('[StudentWorks Debug] Expected sesskey: ' . sesskey());
    
    require_sesskey();
    
    $workid = required_param('workid', PARAM_INT);
    $status = required_param('status', PARAM_ALPHANUMEXT);
    
    error_log('[StudentWorks Debug] Updating work ' . $workid . ' to status ' . $status);

    $result = \local_studentworks\manager\work_manager::update_status($workid, $status);
    
    error_log('[StudentWorks Debug] Update result: ' . ($result ? 'true' : 'false'));

    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

// Handle AJAX filter request.
error_log('[StudentWorks Debug] Checking action: ' . $action);
if ($action === 'filter_works') {
    error_log('[StudentWorks Debug] Action is filter_works, checking sesskey...');
    require_sesskey();
    error_log('[StudentWorks Debug] Sesskey valid, processing request');

    // Debug logging.
    error_log('[StudentWorks Debug] AJAX filter_works called');

    // Get filter parameters from GET request.
    $page = optional_param('page', 0, PARAM_INT);
    $filtertype = optional_param('type', '', PARAM_ALPHANUMEXT);
    $filterstatus = optional_param('status', '', PARAM_ALPHANUMEXT);
    $search = optional_param('search', '', PARAM_TEXT);
    $datefrom = optional_param('date_from', '', PARAM_TEXT);
    $dateto = optional_param('date_to', '', PARAM_TEXT);

    error_log('[StudentWorks Debug] Filter params: type=' . $filtertype . ', status=' . $filterstatus . 
              ', search=' . $search . ', date_from=' . $datefrom . ', date_to=' . $dateto . ', page=' . $page);

    // Use work_manager to get filtered records.
    $filters = [
        'type' => $filtertype,
        'status' => $filterstatus,
        'date_from' => $datefrom,
        'date_to' => $dateto,
        'search' => $search,
    ];

    $result = \local_studentworks\manager\work_manager::get_works($filters, $page, $perpage);
    $records = $result['records'];
    $total = $result['total'];

    error_log('[StudentWorks Debug] Records found: ' . count($records) . ', Total: ' . $total);

    // Render dashboard for AJAX response.
    $output = $PAGE->get_renderer('local_studentworks');
    $dashboard = new \local_studentworks\output\teacher_dashboard(
        $records,
        [
            'type' => $filtertype,
            'status' => $filterstatus,
            'date_from' => $datefrom,
            'date_to' => $dateto,
        ],
        $search,
        $page,
        $perpage,
        true,
        $total
    );

    // Export template data.
    $data = $dashboard->export_for_template($output);

    // Render only the table and pagination sections.
    $html = '';
    if (!empty($data['works'])) {
        // Render table rows.
        $html .= $output->render_from_template('local_studentworks/teacher_dashboard_table', $data);
    } else {
        // Render empty state.
        $html .= $output->render_from_template('local_studentworks/teacher_dashboard_empty', []);
    }

    // Render pagination.
    $pagination = '';
    if (!empty($data['pagination'])) {
        $pagination = $output->render_from_template('local_studentworks/teacher_dashboard_pagination', $data['pagination']);
    }

    // Calculate stats.
    $stats = [
        'total' => $data['stats']['total'] ?? 0,
        'reviewed' => $data['stats']['reviewed'] ?? 0,
        'pending' => $data['stats']['pending'] ?? 0,
        'students' => $data['stats']['students'] ?? 0,
    ];

    header('Content-Type: application/json');
    $response = [
        'success' => true,
        'html' => $html,
        'pagination' => $pagination,
        'stats' => $stats,
        'total' => $total,
        'page' => $page,
    ];
    error_log('[StudentWorks Debug] Response prepared, html length: ' . strlen($html) . ', pagination length: ' . strlen($pagination));
    echo json_encode($response);
    exit;
}

// Handle export.
if (!empty($export)) {
    require_sesskey();

    $filters = [
        'type' => $filtertype,
        'status' => $filterstatus,
        'date_from' => $datefrom,
        'date_to' => $dateto,
        'search' => $search,
    ];

    $result = \local_studentworks\manager\work_manager::get_works($filters, 0, 10000);
    $works = $result['records'];

    $filename = 'studentworks_' . date('Y-m-d') . '.' . ($export === 'excel' ? 'xlsx' : 'csv');

    if ($export === 'excel') {
        \local_studentworks\export\exporter::export_excel($works, $filename);
    } else {
        \local_studentworks\export\exporter::export_csv($works, $filename);
    }
    exit;
}

// Page setup using local_studentworks_page_setup function.
$urlparams = [
    'page' => $page,
    'type' => $filtertype,
    'status' => $filterstatus,
    'date_from' => $datefrom,
    'date_to' => $dateto,
    'search' => $search
];

// Define parameter definitions for filtering.
$paramdefinitions = [
    ['name' => 'page', 'type' => PARAM_INT, 'required' => false, 'default' => 0],
    ['name' => 'type', 'type' => PARAM_ALPHA, 'required' => false, 'default' => ''],
    ['name' => 'status', 'type' => PARAM_ALPHA, 'required' => false, 'default' => ''],
    ['name' => 'date_from', 'type' => PARAM_TEXT, 'required' => false, 'default' => ''],
    ['name' => 'date_to', 'type' => PARAM_TEXT, 'required' => false, 'default' => ''],
    ['name' => 'search', 'type' => PARAM_TEXT, 'required' => false, 'default' => ''],
];

$filteredparams = local_studentworks_page_setup(
    '/local/studentworks/teacher.php',
    $urlparams,
    'incourse',
    get_string('allworks', 'local_studentworks'),
    get_string('allworks', 'local_studentworks'),
    [
        'bodyclasses' => ['teacher-dashboard', 'limitedwidth-off'],
        'hideblocks' => false,
        'paramdefinitions' => $paramdefinitions
    ]
);

// Assign filtered parameters to variables.
if (isset($filteredparams['page'])) {
    $page = $filteredparams['page'];
}
if (isset($filteredparams['type'])) {
    $filtertype = $filteredparams['type'];
}
if (isset($filteredparams['status'])) {
    $filterstatus = $filteredparams['status'];
}
if (isset($filteredparams['date_from'])) {
    $datefrom = $filteredparams['date_from'];
}
if (isset($filteredparams['date_to'])) {
    $dateto = $filteredparams['date_to'];
}
if (isset($filteredparams['search'])) {
    $search = $filteredparams['search'];
}

// Add custom CSS.
$PAGE->requires->css('/local/studentworks/styles.css');

// Load AMD module.
$PAGE->requires->js_call_amd('local_studentworks/studentworks', 'init', ['dashboard']);

// Use work_manager to get records with filtering and pagination.
$filters = [
    'type' => $filtertype,
    'status' => $filterstatus,
    'date_from' => $datefrom,
    'date_to' => $dateto,
    'search' => $search,
];

$result = \local_studentworks\manager\work_manager::get_works($filters, $page, $perpage);
$records = $result['records'];
$total = $result['total'];
$pages = $result['pages'];

// Render output.
$output = $PAGE->get_renderer('local_studentworks');
$dashboard = new \local_studentworks\output\teacher_dashboard(
    $records,
    [
        'type' => $filtertype,
        'status' => $filterstatus,
        'date_from' => $datefrom,
        'date_to' => $dateto,
    ],
    $search,
    $page,
    $perpage,
    true,
    $total
);

echo $OUTPUT->header();
echo $output->render($dashboard);
echo $OUTPUT->footer();
