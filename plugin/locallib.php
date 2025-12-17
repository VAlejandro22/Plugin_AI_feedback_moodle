<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');

/**
 * Feedback plugin for AI-powered automated feedback
 */
class assign_feedback_ai extends assign_feedback_plugin {
    
    /**
     * Get the name of the plugin
     * 
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_ai');
    }
    
    /**
     * Get settings form for the plugin
     * 
     * @param MoodleQuickForm $mform
     */
    public function get_settings(\MoodleQuickForm $mform) {
        global $DB;
        
        // Rubric file upload
        $mform->addElement('filemanager', 'assignfeedback_ai_rubricfile',
            get_string('rubricfile', 'assignfeedback_ai'),
            null,
            array(
                'subdirs' => 0,
                'maxbytes' => 5242880, // 5MB
                'maxfiles' => 1,
                'accepted_types' => array('.pdf')
            )
        );
        $mform->addHelpButton('assignfeedback_ai_rubricfile', 'rubricfile', 'assignfeedback_ai');
        
        // API Key (optional)
        $mform->addElement('text', 'assignfeedback_ai_apikey',
            get_string('apikey', 'assignfeedback_ai'), array('size' => 60));
        $mform->setType('assignfeedback_ai_apikey', PARAM_TEXT);
        $mform->addHelpButton('assignfeedback_ai_apikey', 'apikey', 'assignfeedback_ai');
        
        // Model selection
        $models = array(
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Económico)',
            'gpt-4' => 'GPT-4 (Alta calidad)',
            'gpt-4-turbo' => 'GPT-4 Turbo (Equilibrado)'
        );
        $mform->addElement('select', 'assignfeedback_ai_model',
            get_string('model', 'assignfeedback_ai'), $models);
        $mform->addHelpButton('assignfeedback_ai_model', 'model', 'assignfeedback_ai');
        $mform->setDefault('assignfeedback_ai_model', 'gpt-3.5-turbo');
    }
    
    /**
     * Save the settings for the plugin
     * 
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(\stdClass $data) {
        global $DB;
        
        $config = $DB->get_record('assignfeedback_ai_config', 
            array('assignmentid' => $this->assignment->get_instance()->id));
        
        if (!$config) {
            $config = new stdClass();
            $config->assignmentid = $this->assignment->get_instance()->id;
            $config->timecreated = time();
        }
        
        $config->enabled = true;  // If the plugin is enabled in the form, it's enabled
        $config->apikey = $data->assignfeedback_ai_apikey ?? '';
        $config->model = $data->assignfeedback_ai_model ?? 'gpt-3.5-turbo';
        $config->timemodified = time();
        
        // Handle rubric file upload
        if (!empty($data->assignfeedback_ai_rubricfile)) {
            $this->save_rubric_file($data->assignfeedback_ai_rubricfile, $config);
        }
        
        if (isset($config->id)) {
            $DB->update_record('assignfeedback_ai_config', $config);
        } else {
            $DB->insert_record('assignfeedback_ai_config', $config);
        }
        
        return true;
    }
    
    /**
     * Save and extract rubric file
     * 
     * @param int $draftitemid
     * @param stdClass $config
     */
    private function save_rubric_file($draftitemid, &$config) {
        global $USER;
        
        $fs = get_file_storage();
        $context = $this->assignment->get_context();
        
        // Save file
        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'assignfeedback_ai',
            'rubric',
            $this->assignment->get_instance()->id,
            array('subdirs' => 0, 'maxfiles' => 1)
        );
        
        // Get the uploaded file
        $files = $fs->get_area_files(
            $context->id,
            'assignfeedback_ai',
            'rubric',
            $this->assignment->get_instance()->id,
            'timemodified',
            false
        );
        
        if (!empty($files)) {
            $file = reset($files);
            $config->rubricfile = $file->get_id();
            
            // Extract content from PDF
            try {
                $extractor = new \assignfeedback_ai\content_extractor();
                $config->rubriccontent = $extractor->extract($file);
            } catch (Exception $e) {
                debugging('Error extracting rubric content: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }
    
    /**
     * Get the configuration data
     * 
     * @return bool
     */
    public function get_config_for_external() {
        return false;
    }
    
    /**
     * No text is added to the submission
     * 
     * @param stdClass $submissionorgrade
     * @return string
     */
    public function view(stdClass $submissionorgrade) {
        return '';
    }
    
    /**
     * Return true if this plugin is enabled
     * 
     * @return bool
     */
    public function is_enabled() {
        return true;
    }
    
    /**
     * Return true if this plugin has configuration
     * 
     * @return bool
     */
    public function is_configurable() {
        return true;
    }
}
