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
 * English strings for Student Works plugin.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Student Works';
$string['studentworks:viewown'] = 'View own student works';
$string['studentworks:viewall'] = 'View all student works';
$string['studentworks:upload'] = 'Upload student works';
$string['studentworks:review'] = 'Review student works (upload reviews)';

// Navigation.
$string['mystudentworks'] = 'My Works';
$string['allstudentworks'] = 'All Works';

// Work types.
$string['worktype'] = 'Work Type';
$string['coursework'] = 'Coursework / Course Project';
$string['practice'] = 'Practice Report';
$string['vkr'] = 'Final Qualification Work';

// Work fields.
$string['topic'] = 'Topic';
$string['discipline'] = 'Discipline';
$string['author'] = 'Author';
$string['timecreated'] = 'Created';
$string['timemodified'] = 'Last Modified';
$string['status'] = 'Status';

// Status values.
$string['submitted'] = 'Submitted';
$string['reviewed'] = 'Reviewed';
$string['pending'] = 'Pending Review';
$string['status_submitted'] = 'Submitted';
$string['status_under_review'] = 'Under Review';
$string['status_reviewed'] = 'Reviewed';
$string['status_rejected'] = 'Rejected';
$string['updatestatus'] = 'Update Status';
$string['statusupdatesuccess'] = 'Status updated successfully';
$string['statusupdateerror'] = 'Error updating status';

// Actions.
$string['actions'] = 'Actions';
$string['view'] = 'View';
$string['back'] = 'Back';
$string['download'] = 'Download';
$string['upload'] = 'Upload';
$string['search'] = 'Search';

// Upload and files.
$string['uploadwork'] = 'Upload Work';
$string['viewwork'] = 'View Work';
$string['workdetails'] = 'Work Details';
$string['downloadwork'] = 'Download Work';
$string['downloadreview'] = 'Download Review';
$string['studentfile'] = 'Work File (PDF)';
$string['reviewfile'] = 'Review File (PDF)';
$string['uploadreview'] = 'Upload Review';
$string['updatereview'] = 'Update Review';
$string['allworks'] = 'All Student Works';

// Grade.
$string['grade'] = 'Grade';
$string['setgrade'] = 'Set Grade';
$string['nograde'] = 'No Grade';
$string['checkwork'] = 'Check';

// Empty states.
$string['noworks'] = 'No works uploaded yet';
$string['noworksdesc'] = 'Manage your coursework, practice reports, and final qualification works';
$string['noworksfound'] = 'No works found';
$string['noworksfounddesc'] = 'Try adjusting your search or filters';
$string['uploadfirst'] = 'Upload First Work';
$string['noworkfile'] = 'No work file available';

// Filters.
$string['filterbytype'] = 'Filter by Type';
$string['filterbystatus'] = 'Filter by Status';
$string['filterbydate'] = 'Filter by Date';
$string['filterdatefrom'] = 'Date From';
$string['filterdateto'] = 'Date To';
$string['applyfilters'] = 'Apply Filters';
$string['clearfilters'] = 'Clear Filters';
$string['alltypes'] = 'All Types';
$string['allstatuses'] = 'All Statuses';
$string['searchplaceholder'] = 'Search by topic, discipline, author or email...';

// Dashboard and stats.
$string['myworksdesc'] = 'Manage your coursework, practice reports, and theses';
$string['dashboarddesc'] = 'Overview of all student works';
$string['totalworks'] = 'Total Works';
$string['students'] = 'Students';
$string['statistics'] = 'Statistics';
$string['filters'] = 'Filters';
$string['workslist'] = 'Works List';
$string['empty'] = 'Empty State';
$string['history'] = 'History';
$string['files'] = 'Files';
$string['breadcrumb'] = 'Breadcrumb';
$string['pagination'] = 'Pagination';
$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['page'] = 'Page';

// History entries.
$string['worksubmitted'] = 'Work Submitted';
$string['workuploadedby'] = 'Work uploaded by {$a}';
$string['reviewsubmitted'] = 'Review Submitted';

// Messages and notifications.
$string['duplicatework'] = 'This work has already been uploaded for this discipline';
$string['fileuploaderror'] = 'Error uploading file';
$string['uploadsuccess'] = 'Work uploaded successfully';
$string['reviewsuccess'] = 'Review uploaded successfully';
$string['status_change_subject'] = 'Work Status Change';
$string['status_change_body'] = 'The status of your work "{$a->topic}" has been changed to: {$a->status}';
$string['new_work_subject'] = 'New Student Work';
$string['new_work_body'] = 'Student {$a->student} has uploaded a new work "{$a->topic}" ({$a->discipline})';

// Validation errors.
$string['err_topictoolshort'] = 'Topic must be at least 10 characters long';
$string['err_disciplinetoolshort'] = 'Discipline must be at least 3 characters long';
$string['err_invalidfiletype'] = 'Invalid file type. Only {$a} files are allowed';
$string['required'] = 'Required';
$string['minimumchars'] = 'Minimum {$a} characters';
$string['nocourses'] = 'No enrolled courses found';

// Accessibility.
$string['aria_workscount'] = '{$a} works found';
$string['aria_filterresults'] = 'Filter results';

// Export.
$string['export'] = 'Export';
$string['exportcsv'] = 'Export CSV';
$string['exportexcel'] = 'Export Excel';
$string['student'] = 'Student';
$string['email'] = 'Email';
$string['date'] = 'Date';

// Tasks.
$string['task_cleanup_old_files'] = 'Cleanup old rejected works';

// AJAX loading.
$string['loading'] = 'Loading...';
$string['filtererror'] = 'Error applying filters';
