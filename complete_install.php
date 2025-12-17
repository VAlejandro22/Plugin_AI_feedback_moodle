<?php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir.'/setuplib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->libdir.'/adminlib.php');

// Actualizar usuario admin
$user = $DB->get_record('user', array('username' => 'admin'));
if ($user && $user->password === 'adminsetuppending') {
    $user->password = hash_internal_user_password('Admin123!');
    $user->firstname = 'Admin';
    $user->lastname = 'User';
    $user->email = 'admin@example.com';
    $user->confirmed = 1;
    $DB->update_record('user', $user);
    echo "Admin user updated successfully!\n";
} else {
    echo "Admin user already configured or not found.\n";
}

// Actualizar configuración del sitio
set_config('passwordsaltmain', random_string(20));

echo "Installation completed!\n";
