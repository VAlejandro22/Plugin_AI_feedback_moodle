<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * File serving callback for AI feedback files
 */
function assignfeedback_ai_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    return true;
}
