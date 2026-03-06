<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_studentworks_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026030300) {
        // Define table local_studentworks to be modified.
        $table = new xmldb_table('local_studentworks');

        // Add field studentfile if it doesn't exist.
        $field = new xmldb_field('studentfile', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'topic');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            mtrace("Added field 'studentfile' to local_studentworks table");
        }

        // Add field reviewfile if it doesn't exist.
        $field = new xmldb_field('reviewfile', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'studentfile');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            mtrace("Added field 'reviewfile' to local_studentworks table");
        }

        // Add field timemodified if it doesn't exist.
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            mtrace("Added field 'timemodified' to local_studentworks table");
        }

        // Add foreign key on userid if it doesn't exist.
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        if (!$dbman->key_exists($table, $key)) {
            $dbman->add_key($table, $key);
            mtrace("Added foreign key 'userid' to local_studentworks table");
        }

        // Add index on userid if it doesn't exist.
        $index = new xmldb_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
            mtrace("Added index 'userid_idx' to local_studentworks table");
        }

        // Add index on worktype if it doesn't exist.
        $index = new xmldb_index('worktype_idx', XMLDB_INDEX_NOTUNIQUE, ['worktype']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
            mtrace("Added index 'worktype_idx' to local_studentworks table");
        }

        // Add index on timecreated if it doesn't exist.
        $index = new xmldb_index('timecreated_idx', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
            mtrace("Added index 'timecreated_idx' to local_studentworks table");
        }

        // Update version.
        upgrade_plugin_savepoint(true, 2026030300, 'local', 'studentworks');
        mtrace("Upgraded local_studentworks to version 2026030300");
    }

    if ($oldversion < 2026030301) {
        // Define table local_studentworks to be modified.
        $table = new xmldb_table('local_studentworks');

        // Add field status if it doesn't exist.
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'submitted', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            mtrace("Added field 'status' to local_studentworks table");
        }

        // Add index on status if it doesn't exist.
        $index = new xmldb_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
            mtrace("Added index 'status_idx' to local_studentworks table");
        }

        // Update version.
        upgrade_plugin_savepoint(true, 2026030301, 'local', 'studentworks');
        mtrace("Upgraded local_studentworks to version 2026030301");
    }

    if ($oldversion < 2026030302) {
        // Define table local_studentworks to be modified.
        $table = new xmldb_table('local_studentworks');

        // Add field grade if it doesn't exist.
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            mtrace("Added field 'grade' to local_studentworks table");
        }

        // Update version.
        upgrade_plugin_savepoint(true, 2026030302, 'local', 'studentworks');
        mtrace("Upgraded local_studentworks to version 2026030302");
    }

    return true;
}
