<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'assignfeedback_ai';
$plugin->version = 2024122903;
$plugin->requires = 2022041900; // Moodle 4.0+
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = 'v1.3';
$plugin->dependencies = array(
    'mod_assign' => 2022041900
);
