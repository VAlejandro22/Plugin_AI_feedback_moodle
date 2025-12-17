<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
    array(
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => '\assignfeedback_ai\observer::submission_uploaded',
    ),
);
