<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

/**
 * Privacy Subsystem implementation for assignfeedback_ai
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    
    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        // This plugin stores configuration data
        $collection->add_database_table(
            'assignfeedback_ai_config',
            [
                'assignmentid' => 'privacy:metadata:assignfeedback_ai_config:assignmentid',
                'enabled' => 'privacy:metadata:assignfeedback_ai_config:enabled',
                'apikey' => 'privacy:metadata:assignfeedback_ai_config:apikey',
            ],
            'privacy:metadata:assignfeedback_ai_config'
        );
        
        // This plugin sends data to OpenAI
        $collection->add_external_location_link(
            'openai',
            [
                'submission' => 'privacy:metadata:openai:submission',
                'rubric' => 'privacy:metadata:openai:rubric',
                'taskdescription' => 'privacy:metadata:openai:taskdescription',
            ],
            'privacy:metadata:openai'
        );
        
        return $collection;
    }
    
    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // This plugin doesn't store user-specific data in its own tables
        return new contextlist();
    }
    
    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        // No user data to export
    }
    
    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // No user data to delete
    }
    
    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // No user data to delete
    }
    
    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        // No user data stored
    }
    
    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        // No user data to delete
    }
}
