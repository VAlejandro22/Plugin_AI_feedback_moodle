<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for submission events
 */
class observer {
    
    /**
     * Handle submission uploaded event
     * 
     * @param \core\event\base $event
     */
    public static function submission_uploaded(\core\event\base $event) {
        global $DB, $CFG;
        
        try {
            $eventdata = $event->get_data();
            
            // Get submission
            $submissionid = $eventdata['objectid'];
            $submission = $DB->get_record('assign_submission', ['id' => $submissionid], '*', MUST_EXIST);
            
            // Get assignment ID from submission
            $assignmentid = $submission->assignment;
            
            // Get assignment instance
            $assign = $DB->get_record('assign', ['id' => $assignmentid], '*', MUST_EXIST);
            
            // Check if AI feedback is enabled for this assignment
            $config = $DB->get_record('assignfeedback_ai_config', ['assignmentid' => $assignmentid]);
            
            if (!$config || !$config->enabled) {
                return; // AI feedback not enabled for this assignment
            }
            
            // Don't process if already graded
            $grade = $DB->get_record('assign_grades', [
                'assignment' => $assignmentid,
                'userid' => $submission->userid
            ]);
            
            if ($grade && $grade->grade >= 0) {
                debugging('Submission already graded, skipping AI feedback', DEBUG_DEVELOPER);
                return;
            }
            
            // Process the submission
            self::process_submission($assign, $submission, $config);
            
        } catch (\Exception $e) {
            debugging('Error in AI feedback observer: ' . $e->getMessage(), DEBUG_DEVELOPER);
            // Don't throw exception to avoid breaking the submission process
        }
    }
    
    /**
     * Process a submission with AI feedback
     * 
     * @param \stdClass $assign Assignment record
     * @param \stdClass $submission Submission record
     * @param \stdClass $config AI config record
     */
    private static function process_submission($assign, $submission, $config) {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        
        // Get submission content
        $submissioncontent = self::get_submission_content($submission);
        
        if (empty($submissioncontent)) {
            debugging('No submission content found', DEBUG_DEVELOPER);
            return;
        }
        
        // Get task description
        $taskdescription = $assign->intro ?? 'Sin descripción';
        
        // Get rubric content
        if (empty($config->rubriccontent)) {
            debugging('No rubric content configured', DEBUG_DEVELOPER);
            return;
        }
        
        // Get API key (from config or global settings)
        $apikey = $config->apikey;
        if (empty($apikey)) {
            $apikey = get_config('assignfeedback_ai', 'global_apikey');
        }
        
        if (empty($apikey)) {
            debugging('No API key configured', DEBUG_DEVELOPER);
            return;
        }
        
        // Decrypt API key if it was encrypted
        if (strpos($apikey, 'sk-') !== 0 && strpos($apikey, 'sk-proj-') !== 0) {
            // In production, implement proper decryption
            // For now, we assume it's stored in plain text
        }
        
        // Generate feedback using AI
        $aiservice = new ai_service($apikey, $config->model);
        $result = $aiservice->generate_feedback($taskdescription, $config->rubriccontent, $submissioncontent);
        
        // Save grade and feedback
        self::save_grade_and_feedback($assign, $submission, $result['grade'], $result['feedback']);
    }
    
    /**
     * Get submission content from files or text
     * 
     * @param \stdClass $submission
     * @return string
     */
    private static function get_submission_content($submission) {
        global $DB;
        
        $content = '';
        
        // Try to get file submission
        $filesubmission = $DB->get_record('assignsubmission_file', ['submission' => $submission->id]);
        if ($filesubmission) {
            $content = self::extract_file_content($submission);
        }
        
        // Try to get online text submission
        if (empty($content)) {
            $textsubmission = $DB->get_record('assignsubmission_onlinetext', ['submission' => $submission->id]);
            if ($textsubmission) {
                $content = strip_tags($textsubmission->onlinetext);
            }
        }
        
        return $content;
    }
    
    /**
     * Extract content from submitted files
     * 
     * @param \stdClass $submission
     * @return string
     */
    private static function extract_file_content($submission) {
        global $DB;
        
        $fs = get_file_storage();
        
        // Get the course module ID
        $cm = get_coursemodule_from_instance('assign', $submission->assignment);
        if (!$cm) {
            debugging('Could not find course module for assignment', DEBUG_DEVELOPER);
            return '';
        }
        
        $context = \context_module::instance($cm->id);
        
        $files = $fs->get_area_files(
            $context->id,
            'assignsubmission_file',
            'submission_files',
            $submission->id,
            'timemodified',
            false
        );
        
        if (empty($files)) {
            debugging('No files found in submission', DEBUG_DEVELOPER);
            return '';
        }
        
        $extractor = new content_extractor();
        $content = '';
        
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            
            try {
                debugging('Extracting content from file: ' . $file->get_filename(), DEBUG_DEVELOPER);
                $filecontent = $extractor->extract($file);
                $content .= $filecontent . "\n\n";
            } catch (\Exception $e) {
                debugging('Error extracting file content: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
        
        return trim($content);
    }
    
    /**
     * Save grade and feedback to database
     * 
     * @param \stdClass $assign
     * @param \stdClass $submission
     * @param float $grade
     * @param string $feedback
     */
    private static function save_grade_and_feedback($assign, $submission, $grade, $feedback) {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        
        // Get the course module
        $cm = get_coursemodule_from_instance('assign', $assign->id);
        if (!$cm) {
            debugging('Could not find course module', DEBUG_DEVELOPER);
            return;
        }
        
        $context = \context_module::instance($cm->id);
        $assigninstance = new \assign($context, $cm, null);
        
        // Normalize grade to assignment scale
        $maxgrade = floatval($assign->grade);
        $normalizedgrade = min($grade, 100) * ($maxgrade / 100);
        
        // Create or update grade
        $graderecord = $DB->get_record('assign_grades', [
            'assignment' => $assign->id,
            'userid' => $submission->userid
        ]);
        
        $time = time();
        
        if ($graderecord) {
            $graderecord->grade = $normalizedgrade;
            $graderecord->grader = -1; // System grader
            $graderecord->timemodified = $time;
            $DB->update_record('assign_grades', $graderecord);
        } else {
            $graderecord = new \stdClass();
            $graderecord->assignment = $assign->id;
            $graderecord->userid = $submission->userid;
            $graderecord->timecreated = $time;
            $graderecord->timemodified = $time;
            $graderecord->grader = -1;
            $graderecord->grade = $normalizedgrade;
            $graderecord->attemptnumber = $submission->attemptnumber;
            $graderecord->id = $DB->insert_record('assign_grades', $graderecord);
        }
        
        // Save feedback comment
        $feedbackrecord = $DB->get_record('assignfeedback_comments', [
            'assignment' => $assign->id,
            'grade' => $graderecord->id
        ]);
        
        $feedbacktext = get_string('ai_feedback_generated', 'assignfeedback_ai') . "\n\n" . $feedback;
        
        if ($feedbackrecord) {
            $feedbackrecord->commenttext = $feedbacktext;
            $DB->update_record('assignfeedback_comments', $feedbackrecord);
        } else {
            $feedbackrecord = new \stdClass();
            $feedbackrecord->assignment = $assign->id;
            $feedbackrecord->grade = $graderecord->id;
            $feedbackrecord->commenttext = $feedbacktext;
            $feedbackrecord->commentformat = FORMAT_PLAIN;
            $DB->insert_record('assignfeedback_comments', $feedbackrecord);
        }
        
        debugging('AI feedback saved successfully for user ' . $submission->userid, DEBUG_DEVELOPER);
    }
}
