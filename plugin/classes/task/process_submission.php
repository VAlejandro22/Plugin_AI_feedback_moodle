<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task to process AI feedback for a submission
 */
class process_submission extends \core\task\adhoc_task {
    
    /**
     * Get the task name
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_ai') . ' - Process Submission';
    }
    
    /**
     * Execute the task
     */
    public function execute() {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        
        $data = $this->get_custom_data();
        
        if (!$data || !isset($data->assignmentid) || !isset($data->userid)) {
            mtrace('AI Feedback Task: Invalid data');
            return;
        }
        
        $assignmentid = $data->assignmentid;
        $userid = $data->userid;
        
        mtrace("AI Feedback Task: Processing assignment $assignmentid for user $userid");
        
        // Get the latest submission
        $submission = $DB->get_record_sql(
            "SELECT * FROM {assign_submission} 
             WHERE assignment = ? AND userid = ? AND status = 'submitted'
             ORDER BY attemptnumber DESC, timemodified DESC 
             LIMIT 1",
            [$assignmentid, $userid]
        );
        
        if (!$submission) {
            mtrace('AI Feedback Task: No submitted submission found');
            return;
        }
        
        // Get assignment
        $assign = $DB->get_record('assign', ['id' => $assignmentid]);
        if (!$assign) {
            mtrace('AI Feedback Task: Assignment not found');
            return;
        }
        
        // Get config
        $config = $DB->get_record('assignfeedback_ai_config', ['assignmentid' => $assignmentid]);
        if (!$config || !$config->enabled) {
            mtrace('AI Feedback Task: AI feedback not enabled');
            return;
        }
        
        // Check if already graded
        $grade = $DB->get_record('assign_grades', [
            'assignment' => $assignmentid,
            'userid' => $userid
        ]);
        
        if ($grade && $grade->grade >= 0) {
            mtrace('AI Feedback Task: Already graded, skipping');
            return;
        }
        
        // Process the submission
        $this->process_submission($assign, $submission, $config);
    }
    
    /**
     * Process a submission with AI feedback
     */
    private function process_submission($assign, $submission, $config) {
        global $DB, $CFG;
        
        mtrace('AI Feedback Task: Starting processing');
        
        // Get submission content
        $submissioncontent = $this->get_submission_content($submission);
        
        if (empty($submissioncontent)) {
            mtrace('AI Feedback Task: No content found');
            return;
        }
        
        mtrace('AI Feedback Task: Content length: ' . strlen($submissioncontent));
        
        // Get task description
        $taskdescription = $assign->intro ?? 'Sin descripción';
        
        // Get rubric content
        if (empty($config->rubriccontent)) {
            mtrace('AI Feedback Task: No rubric configured');
            return;
        }
        
        // Get API key
        $apikey = $config->apikey;
        if (empty($apikey)) {
            $apikey = get_config('assignfeedback_ai', 'global_apikey');
        }
        
        if (empty($apikey)) {
            mtrace('AI Feedback Task: No API key');
            return;
        }
        
        mtrace('AI Feedback Task: Using model ' . $config->model);
        
        try {
            // Generate feedback using AI
            $aiservice = new \assignfeedback_ai\ai_service($apikey, $config->model);
            $result = $aiservice->generate_feedback($taskdescription, $config->rubriccontent, $submissioncontent);
            
            mtrace('AI Feedback Task: Got AI response, grade=' . $result['grade']);
            
            // Detect AI-generated content
            $aidetector = new \assignfeedback_ai\ai_detector();
            $detectionresult = $aidetector->detect($submissioncontent);
            $detectiontext = $aidetector->format_result($detectionresult);
            
            // Append AI detection results to feedback
            $finalfeedback = $result['feedback'] . $detectiontext;
            
            // Save grade and feedback
            $this->save_grade_and_feedback($assign, $submission, $result['grade'], $finalfeedback);
            
            mtrace('AI Feedback Task: Completed successfully');
            
        } catch (\Exception $e) {
            mtrace('AI Feedback Task Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get submission content
     */
    private function get_submission_content($submission) {
        global $DB;
        
        $content = '';
        
        // Try file submission
        $filesubmission = $DB->get_record('assignsubmission_file', ['submission' => $submission->id]);
        if ($filesubmission) {
            mtrace('AI Feedback Task: Found file submission');
            $content = $this->extract_file_content($submission);
        }
        
        // Try online text
        if (empty($content)) {
            $textsubmission = $DB->get_record('assignsubmission_onlinetext', ['submission' => $submission->id]);
            if ($textsubmission) {
                mtrace('AI Feedback Task: Found text submission');
                $content = strip_tags($textsubmission->onlinetext);
            }
        }
        
        return $content;
    }
    
    /**
     * Extract content from files
     */
    private function extract_file_content($submission) {
        global $DB;
        
        $fs = get_file_storage();
        
        $cm = get_coursemodule_from_instance('assign', $submission->assignment);
        if (!$cm) {
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
            mtrace('AI Feedback Task: No files in submission');
            return '';
        }
        
        $extractor = new \assignfeedback_ai\content_extractor();
        $content = '';
        
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            
            try {
                mtrace('AI Feedback Task: Extracting: ' . $file->get_filename());
                $filecontent = $extractor->extract($file);
                $content .= $filecontent . "\n\n";
            } catch (\Exception $e) {
                mtrace('AI Feedback Task: Extract error: ' . $e->getMessage());
            }
        }
        
        return trim($content);
    }
    
    /**
     * Save grade and feedback
     */
    private function save_grade_and_feedback($assign, $submission, $grade, $feedback) {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        
        $cm = get_coursemodule_from_instance('assign', $assign->id);
        if (!$cm) {
            return;
        }
        
        // Normalize grade
        $maxgrade = floatval($assign->grade);
        $normalizedgrade = min($grade, 100) * ($maxgrade / 100);
        
        mtrace('AI Feedback Task: Saving grade ' . $normalizedgrade);
        
        // Create or update grade
        $graderecord = $DB->get_record('assign_grades', [
            'assignment' => $assign->id,
            'userid' => $submission->userid
        ]);
        
        $time = time();
        
        if ($graderecord) {
            $graderecord->grade = $normalizedgrade;
            $graderecord->grader = -1;
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
        
        // Save feedback
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
        
        mtrace('AI Feedback Task: Saved for user ' . $submission->userid);
    }
}
