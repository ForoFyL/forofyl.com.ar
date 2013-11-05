<?php
/**
*
* @package phpBB3
* @version $Id: index.php 240 2009-11-01 20:38:17Z lefty74 $
* @copyright (c) 2008 lefty74
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
$action = request_var('action', '');	

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/info_acp_announcement_centre', 'mods/announcement_centre', 'acp/common', 'install'));
$user->theme['template_storedb'] = false;
// CURRENT VERSION
$current_version = '1.2.2';

// Before we do anything, lets see if an Admin is calling this file
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}

$msg = '';
$table_data = array();

if (version_compare($config['version'], '3.0.4', '>' ))
{
	$db_tools = new phpbb_db_tools($db);
	$table_data = get_table_data();
}


switch ($action)
{
	case 'uninstall_complete':
	case 'install_complete':

		$sql = 'DELETE 
		FROM ' . STYLES_TEMPLATE_DATA_TABLE . "
		WHERE template_filename LIKE '" . $db->sql_escape('install_%') . "'";
		$result = $db->sql_query($sql);

		if ($action == 'install_complete')
		{
			$msg .= $user->lang['AC_INSTALL_COMPLETE'];
		}
		elseif ($action == 'uninstall_complete')
		{
			$msg .= $user->lang['AC_DELETE_COMPLETE'];
		}
		
		$msg .= sprintf($user->lang['AC_INSTALL_RETURN'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');

						
		
		// Assign index specific vars
		$template->assign_vars(array(
			'TITLE'	=> $user->lang['ACP_ANNOUNCEMENTS_CENTRE'],
			'BODY'	=> $msg,
		));

	break;
	case 'install':
					
		// Add permissions
		$auth_admin = new auth_admin();
		$auth_admin->acl_add_option(array(
		    'global'   => array('a_announcement_centre', 'm_announcement_centre'),
		));    

		$msg .=  '<span style="color:green;"> - ' . $user->lang['AC_PERM_CREATED'] . '</span><br/>';
		
		if (version_compare($config['version'], '3.0.4', '>' ))
		{
			$db_tools->sql_create_table(ANNOUNCEMENTS_CENTRE_TABLE, $table_data);
		}
		else 
		{
			load_schema($phpbb_root_path . 'install/schemas/');
		}
		
		$msg .=  '<span style="color:green;">- ' . $user->lang['AC_TABLE_CREATED'] . '</span> <br/>';
		
		$uid_text = $bitfield_text = $options_text = ''; // will be modified by generate_text_for_storage
		$uid_text_guests = $bitfield_text_guests = $options_text_guests = ''; // will be modified by generate_text_for_storage
		$uid_draft = $bitfield_draft = $options_draft = ''; // will be modified by generate_text_for_storage
		$allow_bbcode = $allow_urls = $allow_smilies = true;
		$announcement_text = '[color=red][b]Site Announcements[/b][/color] can be seen here!! :mrgreen:';
		$announcement_text_guests = '[color=green][b]Guest Announcements[/b][/color] can be seen here!! :wink:';
		$announcement_draft = '[color=red][b]Draft Announcements[/b][/color] can be seen here!! :mrgreen:';
		
		generate_text_for_storage($announcement_text, $uid_text, $bitfield_text, $options_text, $allow_bbcode, $allow_urls, $allow_smilies);
		generate_text_for_storage($announcement_text_guests, $uid_text_guests, $bitfield_text_guests, $options_text_guests, $allow_bbcode, $allow_urls, $allow_smilies);
		generate_text_for_storage($announcement_draft, $uid_draft, $bitfield_draft, $options_draft, $allow_bbcode, $allow_urls, $allow_smilies);
		
		$sql_ary = array(
		'announcement_title' 						=> 'Site Announcements',
		'announcement_text' 						=> (string) $announcement_text,
		'announcement_gopost' 						=> 0,
		'announcement_forum_id' 					=> 0,
		'announcement_topic_id' 					=> 0,
		'announcement_post_id' 						=> 0,
		'announcement_first_last_post' 				=> 'DESC',
		'announcement_text_bbcode_uid'		 		=> (string) $uid_text,
		'announcement_text_bbcode_bitfield'			=> (string) $bitfield_text,
		'announcement_text_bbcode_options' 			=> (int) 	$options_text,
		'announcement_draft' 						=> (string) $announcement_draft,
		'announcement_draft_bbcode_uid' 			=> (string) $uid_draft,
		'announcement_draft_bbcode_bitfield' 		=> (string) $bitfield_draft,
		'announcement_draft_bbcode_options' 		=> (int) 	$options_draft,
		'announcement_title_guests' 				=> 'Guest Announcements',
		'announcement_text_guests' 					=> (string) $announcement_text_guests,
		'announcement_text_guests_bbcode_uid' 		=> (string) $uid_text_guests,
		'announcement_text_guests_bbcode_bitfield' 	=> (string) $bitfield_text_guests,
		'announcement_text_guests_bbcode_options' 	=> (int) 	$options_text_guests,
		);
		$db->sql_query('INSERT INTO ' . ANNOUNCEMENTS_CENTRE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		
		set_config('announcement_enable', 1);
		set_config('announcement_show_index', 0);
		set_config('announcement_enable_guests', 1);
		set_config('announcement_show', 1);
		set_config('announcement_show_birthdays', 1);
		set_config('announcement_birthday_avatar', 1);
		set_config('announcement_ava_max_size', 40);
		set_config('announcement_enable', 1);


		set_config('announcement_show_birthdays_always', 1);
		set_config('announcement_show_birthdays_and_announce', 1);

		set_config('announcement_show_group', 0);
		set_config('announcement_align', 'center');
		set_config('announcement_guests_align', 'center');
			
		set_config('acmod_version', (string) $current_version);
		
		$msg .=  '<br /><span style="color:green;"> - ' . $user->lang['AC_CONFIGS_CREATED'] . '</span><br/>';

		// install the modules
		install_modules();
		$msg .= '<span style="color:green;">- ' . $user->lang['AC_MODULE_ADDED'] . '</span><br /><br />';

		
		// lets purge the cache
		global $cache;
		$cache->purge();

		add_log('admin', 'LOG_PURGE_CACHE');

		//don't know of a better fix but redirect to make sure to get the install html files out of the templates that are stored in the database after the cache purging
		$redirect = append_sid("{$phpbb_root_path}install/index.$phpEx", "action=install_complete");
		meta_refresh(3, $redirect);

		$msg .= $user->lang['AC_INSTALL_REDIRECT'];
		
		// Assign index specific vars
		$template->assign_vars(array(
			'TITLE'	=> $user->lang['ACP_ANNOUNCEMENTS_CENTRE'],
			'BODY'	=> $msg,
		));

	break;
	
	case 'upgrade':

		if (version_compare((isset($config['acmod_version']) ? $config['acmod_version'] : 0), $current_version, '=='))
		{
			$msg .= sprintf($user->lang['AC_UP_TO_DATE'], $current_version) . '<br />';
			
			$msg .= sprintf($user->lang['AC_INSTALL_RETURN'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');

		}
		else
		{
		
			// lets save the data that's already in the table
			$sql = 'SELECT * 
				FROM ' . ANNOUNCEMENTS_CENTRE_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$announcement = $row;		
			}
			$db->sql_freeresult($result);		

			//lets now delete the table
			if (version_compare($config['version'], '3.0.4', '>' ))
			{
				$db_tools->sql_table_drop(ANNOUNCEMENTS_CENTRE_TABLE);
			}
			else 
			{
				$sql = 'DROP TABLE ' . ANNOUNCEMENTS_CENTRE_TABLE;
				$result = $db->sql_query($sql);		
			}

			$msg .=  '<span style="color:green;">- ' . $user->lang['AC_PREV_TABLE_DELETE'] . '</span> <br/>';

			// create the table
			if (version_compare($config['version'], '3.0.4', '>' ))
			{
				$db_tools->sql_create_table(ANNOUNCEMENTS_CENTRE_TABLE, $table_data);
			}
			else 
			{
				load_schema($phpbb_root_path . 'install/schemas/');
			}
			
			$msg .=  '<span style="color:green;">- ' . $user->lang['AC_TABLE_CREATED'] . '</span> <br/>';

			$sql_ary = array(
			'announcement_title' 						=> (string) $announcement['announcement_title'],
			'announcement_text' 						=> (string) $announcement['announcement_text'],
			'announcement_gopost' 						=> isset($announcement['announcement_gopost']) ? (int)$announcement['announcement_gopost'] : 0,
			'announcement_forum_id' 					=> isset($announcement['announcement_forum_id']) ? (int)$announcement['announcement_forum_id'] : 0,
			'announcement_topic_id' 					=> isset($announcement['announcement_topic_id']) ? (int)$announcement['announcement_topic_id'] : 0,
			'announcement_post_id' 						=> isset($announcement['announcement_post_id']) ? (int)$announcement['announcement_post_id'] : 0,
			'announcement_first_last_post' 				=> isset($announcement['announcement_first_last_post']) ? (string)$announcement['announcement_first_last_post'] : 'DESC',
			'announcement_text_bbcode_uid'		 		=> (string) $announcement['announcement_text_bbcode_uid'],
			'announcement_text_bbcode_bitfield'			=> (string) $announcement['announcement_text_bbcode_bitfield'],
			'announcement_text_bbcode_options' 			=> (int) 	$announcement['announcement_text_bbcode_options'],
			'announcement_draft' 						=> (string) $announcement['announcement_draft'],
			'announcement_draft_bbcode_uid' 			=> (string) $announcement['announcement_draft_bbcode_uid'],
			'announcement_draft_bbcode_bitfield' 		=> (string) $announcement['announcement_draft_bbcode_bitfield'],
			'announcement_draft_bbcode_options' 		=> (int) 	$announcement['announcement_draft_bbcode_options'],
			'announcement_title_guests' 				=> (string) $announcement['announcement_title_guests'],
			'announcement_text_guests' 					=> (string) $announcement['announcement_text_guests'],
			'announcement_text_guests_bbcode_uid' 		=> (string) $announcement['announcement_text_guests_bbcode_uid'],
			'announcement_text_guests_bbcode_bitfield' 	=> (string) $announcement['announcement_text_guests_bbcode_bitfield'],
			'announcement_text_guests_bbcode_options' 	=> (int) 	$announcement['announcement_text_guests_bbcode_options'],
			);
			$db->sql_query('INSERT INTO ' . ANNOUNCEMENTS_CENTRE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					
			if (isset($config['acmod_version']))
			{
				switch ($config['acmod_version'])
				{
					case '1.1.0':
					case '1.1.1':
					case '1.1.2':
					case '1.1.3':
					case '1.1.4':
					case '1.2.0':
					case '1.2.0a':
					case '1.2.1':
						// reinstall the modules
						install_modules();
						$msg .= '<span style="color:green;">- ' . $user->lang['AC_MODULE_READDED'] . '</span><br /><br />';
					break;
				}
			}
			else
			{
				set_config('announcement_enable_guests', (int) $announcement['announcement_enable_guests']);
				set_config('announcement_show', (int) $announcement['announcement_show']);
				set_config('announcement_show_birthdays', (int) $announcement['announcement_show_birthdays']);
				set_config('announcement_birthday_avatar', (int) $announcement['announcement_birthday_avatar']);
				set_config('announcement_show_group', (string) $announcement['announcement_show_group']);
			
				set_config('announcement_ava_max_size', 40);
		
				set_config('announcement_show_birthdays_always', 1);
				set_config('announcement_show_birthdays_and_announce', 1);
		
		
				set_config('announcement_align', 'center');
				set_config('announcement_guests_align', 'center');

				// Add permissions
				$auth_admin = new auth_admin();
				$auth_admin->acl_add_option(array(
					'global'   => array('a_announcement_centre', 'm_announcement_centre'),
				));    
				
				$msg .=  '<span style="color:green;"> - ' . $user->lang['AC_PERM_CREATED'] . '</span><br/>';

				// install the modules
				install_modules();
				$msg .= '<span style="color:green;">- ' . $user->lang['AC_MODULE_READDED'] . '</span><br /><br />';
			}	

			set_config('acmod_version', (string) $current_version);			
			$msg .=  '<br /><span style="color:green;"> - ' . $user->lang['AC_VERSION_UPDATED'] . '</span><br/>';
		
			
			// lets clear the cache 
			global $cache;
			$cache->purge();
			add_log('admin', 'LOG_PURGE_CACHE');
							
			//don't know of a better fix but redirect to make sure to get the install html files out of the templates that are stored in the database after the cache purging
			$redirect = append_sid("{$phpbb_root_path}install/index.$phpEx", "action=install_complete");
			meta_refresh(3, $redirect);
	
			$msg .= $user->lang['AC_INSTALL_REDIRECT'];
		
		}
		// Assign index specific vars
		$template->assign_vars(array(
			'TITLE'	=> $user->lang['ACP_ANNOUNCEMENTS_CENTRE'],
			'BODY'	=> $msg,
		));
	break;

	case 'uninstall':
	
		//lets now delete the table
		if (version_compare($config['version'], '3.0.4', '>' ))
		{
			$db_tools->sql_table_drop(ANNOUNCEMENTS_CENTRE_TABLE);
		}
		else 
		{
			$sql = 'DROP TABLE ' . ANNOUNCEMENTS_CENTRE_TABLE;
			$result = $db->sql_query($sql);		
		}
			
		if (isset($config['acmod_version']))
		{
			$sql = 'DELETE 
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option LIKE '" . $db->sql_escape('%_announcement_centre') . "'";
			$result = $db->sql_query($sql);
		}

		// now lets delete the config fields should they exist
		$announcement_row = array(
		'announcement_enable', 
		'announcement_show_index', 
		);
		
		// in case the uninstall is run before the edits have been removed, lets unset one of these so that the get_announcement_data function is not called and throws an error
		unset($config['announcement_enable']);

		if (isset($config['acmod_version']))
		{
			$announcement_row = array_merge($announcement_row, array(
			'announcement_enable_guests',
			'announcement_show',
			'announcement_show_birthdays',
			'announcement_birthday_avatar',
			'announcement_ava_max_size',
			'announcement_enable',
			'announcement_show_index',
			'announcement_show_birthdays_always',
			'announcement_show_birthdays_and_announce',
			'announcement_show_group',
			'announcement_align',
			'announcement_guests_align',
			'acmod_version',
			));
		}
		foreach ($announcement_row as $config_name)
		{
			if ( isset($config_name) )
			{
				$sql = 'DELETE 
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $db->sql_escape($config_name) . "'";
				$result = $db->sql_query($sql);
			}
		}

		$msg .= '<span style="color:green;">- ' . $user->lang['AC_TABLE_CONFIG_DELETE'] . '</span>';
					
		// install the modules
		install_modules('delete');
		$msg .= '<span style="color:green;">- ' . $user->lang['AC_MODULE_DELETED'] . '</span>';

		//lets purge the cache
		global $cache;
		$cache->purge();
		add_log('admin', 'LOG_PURGE_CACHE');
					
		//don't know of a better fix but redirect to make sure to get the install html files out of the templates that are stored in the database after the cache purging
		$redirect = append_sid("{$phpbb_root_path}install/index.$phpEx", "action=uninstall_complete");
		meta_refresh(3, $redirect);

		$msg .= $user->lang['AC_UNINSTALL_REDIRECT'];
		
		// Assign index specific vars
		$template->assign_vars(array(
			'TITLE'	=> $user->lang['ACP_ANNOUNCEMENTS_CENTRE'],
			'BODY'	=> $msg,
		));
	break;

	default:
			
		$msg = '<span style="color:red; font-size:1.5em;">' . $user->lang['AC_BACKUP_WARN'] . '</span><br /><br />';				

		if (!isset($config['announcement_enable']) && !isset($config['acmod_version']))
		{
			$msg .= '<span style="color:red;">' . $user->lang['AC_INSTALL_DESC'] . '</span><br /><br />';				
			$msg .= '<a href="' . append_sid("{$phpbb_root_path}install/index.$phpEx", "action=install") . '">' . $user->lang['AC_NEW_INSTALL'] . '</a><br />';
		}
		else
		{
			$msg .= '<span style="color:red;">' . $user->lang['AC_UPGRADE_DESC'] . '</span><br /><br />';				

			if (!version_compare((isset($config['acmod_version']) ? $config['acmod_version'] : 0), $current_version, '=='))
			{
				$msg .= '<a href="' . append_sid("{$phpbb_root_path}install/index.$phpEx", "action=upgrade") . '">' . sprintf($user->lang['AC_UPGRADE'], $current_version) . '</a><br />';						
			}
			else
			{
				$msg .= sprintf($user->lang['AC_UP_TO_DATE'], $current_version) . '<br />';
			}
			

			$msg .= '<a href="' . append_sid("{$phpbb_root_path}install/index.$phpEx", "action=uninstall") . '"><br /><span style="color:red;">' . $user->lang['AC_UNINSTALL'] . '</span></a><br />';	
			
		}

		// Assign index specific vars
		$template->assign_vars(array(
			'TITLE'	=> $user->lang['ACP_ANNOUNCEMENTS_CENTRE'],
			'BODY'	=> $msg,
		));

}

// Output

// Output page
page_header($user->lang['INSTALL_PANEL']);

// Set custom template for admin area
$template->set_custom_template($phpbb_root_path . 'adm/style', 'admin');
$template->assign_var('T_TEMPLATE_PATH', $phpbb_root_path . 'adm/style');

$template->set_filenames(array(
	'body' => 'install_main.html')
);

page_footer();

/**
* Load a schema (and execute)
*
* @param string $install_path Path to folder containing schema files
* @param mixed $install_dbms Alternative database system than $dbms
*/
function load_schema($install_path = '', $install_dbms = false)
{
   global $db;
   global $table_prefix;

   if ($install_dbms === false)
   {
	  global $dbms;
	  $install_dbms = $dbms;
   }

   static $available_dbms = false;

   if (!$available_dbms)
   {
	  if (!function_exists('get_available_dbms'))
	  {
		 global $phpbb_root_path, $phpEx;
		 include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	  }

	  $available_dbms = get_available_dbms($install_dbms);

	  if ($install_dbms == 'mysql')
	  {
		 if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
		 {
			$available_dbms[$install_dbms]['SCHEMA'] .= '_41';
		 }
		 else
		 {
			$available_dbms[$install_dbms]['SCHEMA'] .= '_40';
		 }
	  }
   }

   $remove_remarks = $available_dbms[$install_dbms]['COMMENTS'];
   $delimiter = $available_dbms[$install_dbms]['DELIM'];

   $dbms_schema = $install_path . $available_dbms[$install_dbms]['SCHEMA'] . '_schema.sql';

   if (file_exists($dbms_schema))
   {
	  $sql_query = @file_get_contents($dbms_schema);
	  $sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

	  $remove_remarks($sql_query);

	  $sql_query = split_sql_file($sql_query, $delimiter);

	  foreach ($sql_query as $sql)
	  {
		 $db->sql_query($sql);
	  }
	  unset($sql_query);
   }

   if (file_exists($install_path . 'schema_data.sql'))
   {
	  $sql_query = file_get_contents($install_path . 'schema_data.sql');

	  switch ($install_dbms)
	  {
		 case 'mssql':
		 case 'mssql_odbc':
			$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
		 break;

		 case 'postgres':
			$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
		 break;
	  }

	  $sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
	  $sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'adjust_language_keys_callback', $sql_query);

	  remove_remarks($sql_query);

	  $sql_query = split_sql_file($sql_query, ';');

	  foreach ($sql_query as $sql)
	  {
		 $db->sql_query($sql);
	  }
	  unset($sql_query);
   }
}

/**
* Check if the modules exists, then (delete and re-)add the modules
*
*/
function install_modules($type=false)
{
   global $db, $user;

   // Lets make sure this module does not get added a second time by accident
	$sql = 'SELECT module_id
		FROM ' . MODULES_TABLE . "
		WHERE module_langname = '" . $db->sql_escape('ACP_ANNOUNCEMENTS_CENTRE') . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if ($row)
	{
		$sql = 'DELETE
			FROM ' . MODULES_TABLE . "
			WHERE module_langname LIKE '" . $db->sql_escape('%ANNOUNCEMENTS_CENTRE%') . "'
			OR module_basename = 'announce'"
		;
		$result = $db->sql_query($sql);
	}
	
	if ($type != 'delete')
	{
		// Lets get the .MOD module ID so we can insert our module there
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_langname = '" . $db->sql_escape('ACP_CAT_DOT_MODS') . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$_module = new acp_modules();
		
		// So lets add the main category
		$announcement_centre = array(
			'module_basename'	=> '',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> (int) $row['module_id'],
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_ANNOUNCEMENTS_CENTRE',
			'module_mode'		=> '',
			'module_auth'		=> 'acl_a_announcement_centre',
		);
		$_module->update_module_data($announcement_centre);
		// Now the subcategories
		$announcement_centre_sub = array(
			'module_basename'	=> 'announcements_centre',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> (int) $announcement_centre['module_id'],
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_ANNOUNCEMENTS_CENTRE',
			'module_mode'		=> 'announcements',
			'module_auth'		=> 'acl_a_announcement_centre',
		);
		$_module->update_module_data($announcement_centre_sub);
		$announcement_centre_config = array(
			'module_basename'	=> 'announcements_centre',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> (int) $announcement_centre['module_id'],
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_ANNOUNCEMENTS_CENTRE_CONFIG',
			'module_mode'		=> 'config',
			'module_auth'		=> 'acl_a_announcement_centre',
		);
		$_module->update_module_data($announcement_centre_config);	

		// Now, lets do the MCP part
		$announcement_centre_mcp = array(
			'module_basename'	=> '',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> 0,
			'module_class'		=> 'mcp',
			'module_langname'	=> 'MCP_ANNOUNCEMENTS_CENTRE',
			'module_mode'		=> '',
			'module_auth'		=> 'acl_m_announcement_centre',
		);
		$_module->update_module_data($announcement_centre_mcp);
		// Now the subcategorie
		$announcement_centre_mcp_sub = array(
			'module_basename'	=> 'announce',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> (int) $announcement_centre_mcp['module_id'],
			'module_class'		=> 'mcp',
			'module_langname'	=> 'MCP_ANNOUNCE_FRONT',
			'module_mode'		=> 'front',
			'module_auth'		=> 'acl_m_announcement_centre',
		);
		$_module->update_module_data($announcement_centre_mcp_sub);	
	
	}
}

function get_table_data()
{
	//lets set up the column data for the creation of the new table as of 3.0.5
	$table_data = array(
	'COLUMNS'      => array(
		'announcement_forum_id'         			=> array('UINT', 0),
		'announcement_topic_id'   					=> array('UINT', 0),
		'announcement_post_id'   					=> array('UINT', 0),
		'announcement_gopost'   					=> array('BOOL', 0),
		'announcement_first_last_post'  			=> array('VCHAR:4', 0),
		'announcement_draft'   						=> array('TEXT_UNI', ''),
		'announcement_draft_bbcode_uid'   			=> array('VCHAR:8', ''),
		'announcement_draft_bbcode_bitfield'  		=> array('VCHAR:255', ''),
		'announcement_draft_bbcode_options'			=> array('UINT:11', 7),
		'announcement_text'   						=> array('TEXT_UNI', ''),
		'announcement_text_bbcode_uid'   			=> array('VCHAR:8', ''),
		'announcement_text_bbcode_bitfield'  		=> array('VCHAR:255', ''),
		'announcement_text_bbcode_options'			=> array('UINT:11', 7),
		'announcement_text_guests'   				=> array('TEXT_UNI', ''),
		'announcement_text_guests_bbcode_uid'   	=> array('VCHAR:8', ''),
		'announcement_text_guests_bbcode_bitfield'  => array('VCHAR:255', ''),
		'announcement_text_guests_bbcode_options'	=> array('UINT:11', 7),
		'announcement_title'  						=> array('VCHAR:255', ''),
		'announcement_title_guests'  				=> array('VCHAR:255', ''),
	),
	);

	return $table_data;
}

?>