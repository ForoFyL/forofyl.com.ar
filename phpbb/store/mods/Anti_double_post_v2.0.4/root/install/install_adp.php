<?php

/** 
*
* install script to set up permission options in the db for foo mod
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/

// initialize the page
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/anti_double_post');


// Setup $auth_admin class so we can add tabulated survey permission options
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
$auth_admin = new auth_admin();

// Add foo permissions as local permissions
// (you could instead make them global permissions by making the obvious changes below)
$auth_admin->acl_add_option(array(
    'local'        => array(),
    'global'    => array('u_adp_allow')
));


$message = $user->lang['ADDED_PERMISSIONS'] . '<br /><br />';
$message .= $user->lang['REMOVE_INSTALL'];
trigger_error($message);

?>