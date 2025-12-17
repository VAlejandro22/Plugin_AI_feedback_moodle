<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for assignfeedback_ai
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_assignfeedback_ai_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    // Add future upgrade steps here
    
    return true;
}
