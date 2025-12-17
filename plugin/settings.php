<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Global API Key setting
    $settings->add(new admin_setting_configtext(
        'assignfeedback_ai/global_apikey',
        get_string('global_apikey', 'assignfeedback_ai'),
        get_string('global_apikey_desc', 'assignfeedback_ai'),
        '',
        PARAM_TEXT,
        60
    ));
    
    // Global model setting
    $models = array(
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-4' => 'GPT-4',
        'gpt-4-turbo' => 'GPT-4 Turbo'
    );
    
    $settings->add(new admin_setting_configselect(
        'assignfeedback_ai/global_model',
        get_string('global_model', 'assignfeedback_ai'),
        get_string('global_model_desc', 'assignfeedback_ai'),
        'gpt-3.5-turbo',
        $models
    ));
}
