<?php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir.'/moodlelib.php');

$user = get_complete_user_data('username', 'admin');
$user->password = hash_internal_user_password('Admin123!');
user_update_user($user, false);
echo "Password updated successfully\n";
