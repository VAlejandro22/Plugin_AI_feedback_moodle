<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$observers = array(
    // When a file is uploaded
    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
    // When online text is saved
    array(
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
    // When the submission is actually submitted (main event for first submission)
    array(
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
    // When submission status is updated (catches submit action)
    array(
        'eventname' => '\mod_assign\event\submission_status_updated',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
);
