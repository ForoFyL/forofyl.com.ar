<?php

/**
*
* @author RMcGirr83 (Rich McGirr) rmcgirr83@gmail.com 
* @package umil
* @version $Id contact_install.php 1.0.10 2010-01-22 09:10:00Z RMcGirr83 $
* @copyright (c) 2009 RMcGirr83 
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
$language_file = 'mods/info_acp_contact';

// The name of the mod to be displayed during installation.
$mod_name = 'ACP_CONTACT_TITLE';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'contact_version';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 1.0.6
	'1.0.6'	=> array(

		// Add a table
		'table_add' => array(
			array('phpbb_contact_config', array(
					'COLUMNS'		=> array(
						'contact_confirm'			=> array('BOOL', 1),
						'contact_confirm_guests'	=> array('BOOL', 1),
						'contact_max_attempts'		=> array('UINT:3',0),
						'contact_method'			=> array('BOOL', 0),
						'contact_bot_user'			=> array('UINT', 0),
						'contact_bot_forum'			=> array('UINT', 0),
						'contact_reasons'			=> array('MTEXT_UNI', ''),
						'contact_founder_only'		=> array('BOOL', 0),
						'contact_bbcodes_allowed'	=> array('BOOL', 0),
						'contact_smilies_allowed'	=> array('BOOL', 0),
						'contact_bot_poster'		=> array('BOOL', 0),
						'contact_attach_allowed'	=> array('BOOL', 0),
					),
				),
			),			
		),

		// Lets add a config setting and set it to false
		'config_add' => array(
			array('contact_enable', false),
		),		
		
		// Now to add some permission settings
		'permission_add' => array(
			array('a_contact'),
		),
		
		// How about we give some default permissions then as well?
		// Admins can do anything with the contact mod
		'permission_set' => array(
			// Global Group permissions
			array('ADMINISTRATORS', 'a_contact', 'group'),
			// Global Role permissions for admins
			array('ROLE_ADMIN_FULL', 'a_contact'),
		),
		
		// and last but not least...a module
		'module_add' => array(
			// First, lets add a new category named ACP_CAT_CONTACT to ACP_CAT_DOT_MODS
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CAT_CONTACT'),

			// next let's add our module
			array('acp', 'ACP_CAT_CONTACT', array(
					'module_basename'	=> 'contact',
					'modes'				=> array('configuration'),
					'module_auth'		=> 'a_contact',
				),
			),
		),		
	),

	// Version 1.0.7
	'1.0.7'	=> array(
		// Add a column
		'table_column_add' => array(
			array('phpbb_contact_config', 'contact_urls_allowed', array('BOOL', 0),
			),
		),
	),
	// Version 1.0.8
	'1.0.8'	=> array(
		// nothing changed in this version
	),
	// Version 1.0.9
	'1.0.9'	=> array(
		// Add a column
		'table_column_add' => array(
			array('phpbb_contact_config', 'contact_username_chk', array('BOOL', 0),
			),
			array('phpbb_contact_config', 'contact_email_chk', array('BOOL', 0),
			),
		),
		// now install the entries in the table
		// see if it exists from prior versions
		'custom'	=> 'contact_table',		
	),
	
	// Version 1.0.10
	'1.0.10'	=> array(
		// nothing changed in this version
	),	
		
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/*
* Here is our custom function that will be called
*
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function contact_table($action, $version)
{
	global $cache, $config, $db, $table_prefix, $umil;

	if ($action == 'update' && version_compare($config['contact_version'], '1.0.6', '<'))
	{
		
		// remove the module...we add a new one for the .MODS tab
		$umil->module_remove('acp', 'ACP_BOARD_CONFIGURATION', 'ACP_CONTACT_ADMIN_SETTINGS');
		
		// update entries from previous version
		$sql_ary = array(
			'contact_confirm'			=> $config['contact_confirm'],
			'contact_confirm_guests'	=> $config['contact_confirm_guests'],
			'contact_max_attempts'		=> $config['contact_max_attempts'],
			'contact_method'			=> $config['contact_method'],
			'contact_bot_user'			=> $config['contact_bot_user'],
			'contact_bot_forum'			=> $config['contact_bot_forum'],
			'contact_reasons'			=> $config['contact_reasons'],
			'contact_founder_only'		=> $config['contact_founder_only'],
			'contact_bbcodes_allowed'	=> $config['contact_bbcodes_allowed'],
			'contact_smilies_allowed'	=> $config['contact_smilies_allowed'],
			'contact_bot_poster'		=> $config['contact_bot_poster'],
			'contact_attach_allowed'	=> 0,
		);
		
		$sql = 'INSERT INTO ' . $table_prefix . 'contact_config ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		// remove original entries from config table
		$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
			WHERE ' . $db->sql_in_set('config_name', array('contact_enable', 'contact_confirm', 'contact_confirm_guests', 'contact_max_attempts', 'contact_method', 'contact_bot_user', 'contact_bot_forum', 'contact_version', 'contact_reasons', 'contact_founder_only', 'contact_bbcodes_allowed', 'contact_smilies_allowed', 'contact_bot_poster'));
		$db->sql_query($sql);
		
		$cache->purge();		
		return 'CONTACT_UPDATED';
	}
	elseif ($action =='install')
	{
		$sql_ary = array(
			'contact_confirm'				=> 1,
			'contact_confirm_guests'        => 1,
			'contact_max_attempts'          => 3,
			'contact_method'				=> 0,
			'contact_bot_user'				=> 2,
			'contact_bot_forum'				=> 2,
			'contact_reasons'				=> '',
			'contact_founder_only'          => 0,
			'contact_bbcodes_allowed'       => 0,
			'contact_smilies_allowed'       => 0,
			'contact_bot_poster'            => 0,
			'contact_attach_allowed'        => 0,
			'contact_username_chk'        	=> 0,
			'contact_email_chk'        		=> 0,
			);
			
		$sql = 'INSERT INTO ' . $table_prefix . 'contact_config ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		return 'CONTACT_INSTALLED';
	}
}
?>