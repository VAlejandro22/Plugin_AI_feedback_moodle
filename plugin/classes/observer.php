<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for submission events
 */
class observer {
    
    /** @var array Track processed submissions to avoid duplicates */
    private static $processed = [];
    
    /**
     * Handle submission uploaded event
     * 
     * @param \core\event\base $event
     */
    public static function submission_uploaded(\core\event\base $event) {
        global $DB, $CFG;
        
        try {
            $eventdata = $event->get_data();
            $eventname = $eventdata['eventname'] ?? '';
            
            debugging('AI Feedback: Processing event ' . $eventname, DEBUG_DEVELOPER);
            
            // Get context to find the assignment
            $contextid = $eventdata['contextid'];
            $context = \context::instance_by_id($contextid);
            
            // Get course module
            if ($context->contextlevel != CONTEXT_MODULE) {
                debugging('AI Feedback: Not a module context', DEBUG_DEVELOPER);
                return;
            }
            
            $cm = get_coursemodule_from_id('assign', $context->instanceid);
            if (!$cm) {
                debugging('AI Feedback: Not an assign module', DEBUG_DEVELOPER);
                return;
            }
            
            $assignmentid = $cm->instance;
            
            // Get user ID from event
            $userid = $eventdata['userid'];
            
            // Avoid processing the same submission multiple times within 30 seconds
            $cachekey = $assignmentid . '_' . $userid . '_' . floor(time() / 30);
            if (isset(self::$processed[$cachekey])) {
                debugging('AI Feedback: Recently processed, skipping', DEBUG_DEVELOPER);
                return;
            }
            
            // Check if AI feedback is enabled for this assignment
            $config = $DB->get_record('assignfeedback_ai_config', ['assignmentid' => $assignmentid]);
            
            if (!$config || !$config->enabled) {
                debugging('AI Feedback: Not enabled for assignment ' . $assignmentid, DEBUG_DEVELOPER);
                return;
            }
            
            // Check if already graded
            $grade = $DB->get_record('assign_grades', [
                'assignment' => $assignmentid,
                'userid' => $userid
            ]);
            
            if ($grade && $grade->grade >= 0) {
                debugging('AI Feedback: Already graded, skipping', DEBUG_DEVELOPER);
                return;
            }
            
            // Mark as processed
            self::$processed[$cachekey] = true;
            
            debugging('AI Feedback: Scheduling task for assignment ' . $assignmentid . ' user ' . $userid, DEBUG_DEVELOPER);
            
            // Schedule an adhoc task to process after a short delay
            // This ensures all data is saved before we try to process
            $task = new \assignfeedback_ai\task\process_submission();
            $task->set_custom_data((object)[
                'assignmentid' => $assignmentid,
                'userid' => $userid,
            ]);
            // Run after 3 seconds to ensure data is committed
            $task->set_next_run_time(time() + 3);
            
            // Check if a similar task is already queued
            $existingtasks = $DB->get_records_select(
                'task_adhoc',
                "classname = ? AND nextruntime > ?",
                ['\assignfeedback_ai\task\process_submission', time() - 30]
            );
            
            $alreadyqueued = false;
            foreach ($existingtasks as $existingtask) {
                $existingdata = json_decode($existingtask->customdata);
                if ($existingdata && 
                    isset($existingdata->assignmentid) && $existingdata->assignmentid == $assignmentid &&
                    isset($existingdata->userid) && $existingdata->userid == $userid) {
                    $alreadyqueued = true;
                    break;
                }
            }
            
            if (!$alreadyqueued) {
                \core\task\manager::queue_adhoc_task($task, true);
                debugging('AI Feedback: Task queued successfully', DEBUG_DEVELOPER);
            } else {
                debugging('AI Feedback: Task already queued, skipping', DEBUG_DEVELOPER);
            }
            
        } catch (\Exception $e) {
            debugging('AI Feedback Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
