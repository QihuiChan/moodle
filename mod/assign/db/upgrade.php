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
 * Upgrade code for install
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this assignment instance - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_assign_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2018120500) {
        // Define field hidegrader to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field('hidegrader', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'blindmarking');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2018120500, 'assign');
    }

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2021110901) {
        // Define field activity to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field('activity', XMLDB_TYPE_TEXT, null, null, null, null, null, 'alwaysshowdescription');

        // Conditionally launch add field activity.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('activityformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'activity');

        // Conditionally launch add field activityformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'cutoffdate');

        // Conditionally launch add field timelimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissionattachments', XMLDB_TYPE_INTEGER, '2',
            null, XMLDB_NOTNULL, null, '0', 'activityformat');

        // Conditionally launch add field submissionattachments.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('assign_submission');
        $field = new xmldb_field('timestarted', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field timestarted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timelimit to be added to assign_overrides.
        $table = new xmldb_table('assign_overrides');
        $field = new xmldb_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'cutoffdate');

        // Conditionally launch add field timelimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2021110901, 'assign');
    }

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2022071200) {
        $submissions = $DB->get_records('assign_submission', null, '', 'id, assignment, userid, groupid, attemptnumber, latest');
        $latestbyassignanduser = [];
        foreach ($submissions as $submission) {
            $assignid = $submission->assignment;
            $userid = $submission->userid;

            $id = isset($latestbyassignanduser[$assignid][$userid]) ? $latestbyassignanduser[$assignid][$userid] : 0;
            if ($submission->id > $id) {
                $latestbyassignanduser[$assignid][$userid] = $submission->id;
            }
        }

        $latestbyassignandgroup = [];
        foreach ($submissions as $submission) {
            $assignid = $submission->assignment;
            $groupid = $submission->groupid;

            $id = isset($latestbyassignandgroup[$assignid][$groupid]) ? $latestbyassignandgroup[$assignid][$groupid] : 0;
            if ($submission->id > $id) {
                $latestbyassignandgroup[$assignid][$groupid] = $submission->id;
            }
        }

        $allmostrecentidsuser = array_merge(...$latestbyassignanduser);
        $allmostrecentidsgroup = array_merge(...$latestbyassignandgroup);
        $allmostrecentids = array_unique(array_merge($allmostrecentidsgroup, $allmostrecentidsuser), SORT_REGULAR);

        $idstofix = [];
        foreach ($allmostrecentids as $id) {
            if ($submissions[$id]->latest == 0) {
                $idstofix[] = $id;
            }
        }

        if (count($idstofix)) {
            // $placeholder = '';
            // for ($i = 0; $i < count($idstofix); $i++) {
            //     $placeholder .=  ($i == count($idstofix) - 1) ? "?" : "?,";
            // }

            [$insql, $inparams] = get_in_or_equal($idstofix);

            $DB->execute(
                "UPDATE {assign_submission} SET latest = 1 WHERE id $insql",
                $inparams
            );
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2022071200, 'assign');
    }
    return true;
}
