<?php
/**
*
* @author Палыч  varzaev@mail.ru
* @package umil
* @version $Id: install_thanks_mod.php 128 2010-06-05 10:02:51Палыч $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'THANKS_INSTALLED';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'thanks_mod_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/thanks_mod';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)

$options = array(
	'test_username'	=> array('lang' => 'TEST_USERNAME', 'type' => 'text:40:255', 'explain' => true, 'default' => $user->data['username'], 'select_user' => true),
	'test_boolean'	=> array('lang' => 'TEST_BOOLEAN', 'type' => 'radio:yes_no', 'default' => true),
);
*/
/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
$logo_img = '';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 1.2.5
	'1.2.5'	=> array(
	// Lets add a config setting 
		'config_add' => array(
			array('remove_thanks', '1'),
			array('thanks_postlist_view', '1'),
			array('thanks_profilelist_view', '1'),
			array('thanks_counters_view', '1'),
			array('thanks_number', '100'),
			array('thanks_info_page', '1'),
			array('thanks_only_first_post', '0'),
		),
	
	// Now to add some permission settings
		'permission_add' => array(
			array('f_thanks', false),
			array('u_viewthanks', true),
		),

	// How about we give some default permissions then as well?
		'permission_set' => array(

			// Global Group permissions
			array('REGISTERED', 'u_viewthanks', 'group'),

			// Local Permissions (local permissions can not be set for groups)
			array('ROLE_FORUM_STANDARD', 'f_thanks'),
		),

	// Now to add a table (this uses the layout from develop/create_schema_files.php and from phpbb_db_tools)
		'table_add' => array(
			array($table_prefix . 'thanks', array(
					'COLUMNS'		=> array(
						'post_id'		=> array('UINT', 0),
						'poster_id'		=> array('UINT', 0),
						'user_id'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('post_id', 'user_id'),
					),
				),
			),

	// Lets add a new column to the phpbb_users table 
		'table_column_add' => array(
			array($table_prefix . 'users', 'user_allow_thanks_pm', array('BOOL', 0)),
		),

	// Now lets add some modules to the ACP
		'module_add' => array(

            array('acp', 'ACP_MESSAGES', array(
					'module_basename'	=> 'thanks',
					'module_langname'	=> 'ACP_THANKS',
					'module_mode'		=> 'thanks',
					'module_auth'		=> 'acl_a_board',
				),
			),
		),

		'cache_purge' => array(
			array('imageset', 0),
			array('theme', 0),
			array(),
		),
	),
	// Version 1.2.6	
	//*	module_remove($class, $parent = 0, $module = '')
	'1.2.6'	=> array(
		'module_remove' => array(
            array('acp', 'ACP_MESSAGES', 'ACP_THANKS'),
		),
	// Lets add a config setting 
		'config_add' => array(
			array('thanks_number_post', '10'),
		),	
		'cache_purge' => array(
			array('imageset', 0),
			array('theme', 0),
			array(),
		),
	),	
	// Version 1.2.7		
	'1.2.7'	=> array(
	// Now lets add some modules to the ACP
		'module_add' => array(
            array('acp', 'ACP_CAT_DOT_MODS', 'ACP_THANKS'),
		    array('acp', 'ACP_THANKS', array(
					'module_basename'	=> 'thanks',
					'module_langname'	=> 'ACP_THANKS_SETTINGS',
					'module_mode'		=> 'thanks',
					'module_auth'		=> 'acl_a_board',
				),
			),		
            array('acp', 'ACP_THANKS', array(
					'module_basename'	=> 'thanks_refresh',
					'module_langname'	=> 'ACP_THANKS_REFRESH',
					'module_mode'		=> 'thanks',
					'module_auth'		=> 'acl_a_board',
				),
			),	
            array('acp', 'ACP_THANKS', array(
					'module_basename'	=> 'thanks_truncate',
					'module_langname'	=> 'ACP_THANKS_TRUNCATE',
					'module_mode'		=> 'thanks',
					'module_auth'		=> 'acl_a_board',
				),
			),	
		),
	),	
	// Version 1.2.8		
	'1.2.8'	=> array(
	// Lets add a config setting 
		'config_add' => array(
			array('thanks_post_reput_view', '1'),
			array('thanks_topic_reput_view', '1'),
			array('thanks_forum_reput_view', '0'),
			array('thanks_reput_height', '15'),
			array('thanks_reput_level', '10'),
			array('thanks_number_digits', '2'),
			array('thanks_number_row_reput', '5'),
			array('thanks_reput_graphic', '1'),
			array('thanks_reput_image', 'styles/prosilver/theme/images/reput_star_gold.gif'),
			array('thanks_reput_image_back', 'styles/prosilver/theme/images/reput_star_back.gif'),
			array('thanks_time_view', '1'),
			array('thanks_top_number', '5'),
		),
	// Now lets add some modules to the ACP
		'module_add' => array(
            array('acp', 'ACP_THANKS', array(
					'module_basename'	=> 'thanks_reput',
					'module_langname'	=> 'ACP_THANKS_REPUT_SETTINGS',
					'module_mode'		=> 'thanks',
					'module_auth'		=> 'acl_a_board',
				),
			),	
		),
	// Now to add some permission settings
		'permission_add' => array(
			array('u_viewtoplist', true),
		),

	// How about we give some default permissions then as well?
		'permission_set' => array(

			// Global Group permissions
			array('REGISTERED', 'u_viewtoplist', 'group'),
		),
	// Lets add a new column to the phpbb_thanks 
		'table_column_add' => array(
			array($table_prefix . 'thanks', 'topic_id', array('UINT', 0)),
			array($table_prefix . 'thanks', 'forum_id', array('UINT', 0)),
			array($table_prefix . 'thanks', 'thanks_time', array('UINT:11', 0)),
		),
	// update thanks table	
		'custom'	=> 'update_thanks_table',
	//Cache purge		
		'cache_purge' => array(
			array('imageset', 0),
			array('theme', 0),
			array(),
		),
	),	
	// Version 1.2.9	
	'1.2.9'	=> array(
	// Lets add a new column to the phpbb_users table 
		'table_column_add' => array(
			array($table_prefix . 'users', 'user_allow_thanks_email', array('BOOL', 0)),
		),
	),	
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/*
*
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function update_thanks_table($action, $version)
{
	global $db, $table_prefix, $umil;

	$sql = 'UPDATE '.$table_prefix.'thanks
		SET forum_id = (SELECT forum_id 
		FROM '.$table_prefix.'posts 
		WHERE post_id = '.$table_prefix.'thanks.post_id)';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	$sql = 'UPDATE '.$table_prefix.'thanks	
		SET topic_id = (SELECT topic_id
		FROM '.$table_prefix.'posts
		WHERE post_id = '.$table_prefix.'thanks.post_id)';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	/**
	* Return a string
	* 	The string will be shown as the action performed (command).  It will show any SQL errors as a failure, otherwise success
	*/
	return 'THANKS_CUSTOM_FUNCTION';

	/**
	* Return an array
	* 	With the keys command and result to specify the command and the result
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	/* return array(
		'command'	=> 'EXAMPLE_CUSTOM_FUNCTION',
		'result'	=> 'FAIL',
	);*/

	/**
	* Return an array
	* 	With the keys command and result (same as above) with an array for the command.
	*	With an array for the command it will use sprintf the first item in the array with the following items.
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	/* return array(
		'command'	=> array(
			'EXAMPLE_CUSTOM_FUNCTION',
			$username,
			$number,
			$etc,
		),
		'result'	=> 'FAIL',
	);*/
}
?>