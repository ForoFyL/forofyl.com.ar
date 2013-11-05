<?php
/**
*
* @package phpBB Statistics
* @version $Id: install_install.php 167 2011-02-09 01:07:15Z marc1706 $
* @copyright (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Board3 Portal Installer (www.board3.de)
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{

	if ($this->installed_version)
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'CREATE_TABLE', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $cache, $phpEx;

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['INSTALL_INTRO'],
					'BODY'			=> $user->lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=create_table",
				));

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				set_stats_config('stats_version', NEW_PHPBB_STATS_VERSION, true);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEW_PHPBB_STATS_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_STATS'],
					'U_ACTION'	=> append_sid($phpbb_root_path . 'stats.' . $phpEx),
				));


			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $db, $user, $template, $phpbb_root_path, $phpEx, $cache;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$this->page_title = $user->lang['STAGE_CREATE_TABLE'];
		$s_hidden_fields = '';

		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		// Create the tables
		stats_create_table(array('phpbb_stats_config', 'phpbb_stats_addons', 'phpbb_stats_addons_config', 'phpbb_stats_smilies', 'phpbb_stats_bbcodes'), $dbms_data);

		// Set default config
		set_stats_config('stats_enable', '1');
		set_stats_config('basic_basic_enable', '1');
		set_stats_config('basic_advanced_enable', '1');
		set_stats_config('basic_advanced_security', '0');
		set_stats_config('basic_advanced_pretend_version', '1');
		set_stats_config('basic_miscellaneous_enable', '1');
		set_stats_config('basic_miscellaneous_hide_warnings', '1');
		set_stats_config('activity_forums_enable', '1');
		set_stats_config('activity_topics_enable', '1');
		set_stats_config('activity_users_enable', '1');
		set_stats_config('activity_users_hide_anonymous', '1');
		set_stats_config('contributions_attachments_enable', '1');
		set_stats_config('contributions_polls_enable', '1');
		set_stats_config('periodic_daily_enable', '1');
		set_stats_config('periodic_monthly_enable', '1');
		set_stats_config('periodic_hourly_enable', '1');
		set_stats_config('settings_board_enable', '1');
		set_stats_config('settings_profile_enable', '1');
		set_stats_config('resync_stats_bbcodes', '0');
		set_stats_config('resync_stats', '1');
		set_stats_config('resync_stats_last_sync', '1');
		
		// This will not work with all supported SQL Databases, so we need to distinguish
		switch($db_schema)
		{
			case 'mysql_40':
			case 'mysql_41':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD field_stats_show TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0';
			break;
			
			case 'mssql':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD field_stats_show int NOT NULL DEFAULT (0)';
			break;
			
			case 'postgres':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD COLUMN field_stats_show INT2 DEFAULT 0 NOT NULL CHECK (field_stats_show >= 0)';
			break;
			
			case 'sqlite':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD COLUMN field_stats_show INTEGER UNSIGNED NOT NULL DEFAULT 0';
			break;
			
			case 'firebird':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD field_stats_show INTEGER DEFAULT 0 NOT NULL';
			break;
			
			case 'oracle':
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_TABLE . ' ADD field_stats_show number(1) DEFAULT 0 NOT NULL';
			break;
			
			default:
				trigger_error($user->lang['UNSUPPORTED_DB']);
			break;
		}
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		
		// Add permissions
		$auth_admin = new auth_admin();
		$auth_admin->acl_add_option(array(
			'local'			=> array(),
			'global'		=> array('u_view_stats')
		));
		$cache->destroy('acl_options');
		
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = 'u_view_stats'";
		$result = $db->sql_query($sql);
		$auth_option_id = $db->sql_fetchfield('auth_option_id');
		$db->sql_freeresult($result);

		$sql = 'SELECT DISTINCT(acg.group_id) AS group_id, acr.role_id, acr.role_type, acg.auth_role_id 
				FROM ' . ACL_ROLES_TABLE . ' acr, ' . ACL_GROUPS_TABLE . " acg
				WHERE acr.role_type = 'a_'
					AND acr.role_id = acg.auth_role_id";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);
		
		// Give the wanted role its option
		$roles_data = array(
			'group_id'			=> $group_id,
			'auth_option_id'	=> $auth_option_id,
			'auth_setting'		=> 1,
		);

		$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $roles_data);
		$db->sql_query($sql);


		$submit = $user->lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_CREATE_TABLE'],
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $phpEx, $db, $phpbb_root_path;
		
		$start = request_var('start_sql', 0); 

		if($start < 1)
		{
			//	Get parent_id
			$sql = 'SELECT module_id
					FROM ' . MODULES_TABLE . "
					WHERE module_langname = 'ACP_MODULE_MANAGEMENT'";
			$result = $db->sql_query($sql);
			$parent_id = (int) $db->sql_fetchfield('module_id');
			$db->sql_freeresult($result);
			

			// Create module configuration modules
			$acp_stats = array('module_basename' => 'modules',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $parent_id,	'module_class' => 'acp',	'module_langname'=> 'STATS',	'module_mode' => 'stats',	'module_auth' => 'acl_a_modules');
			add_module($acp_stats);
			// Create STATS_BASIC category + modules
			$acp_stats_basic = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_BASIC');
			add_module($acp_stats_basic);
			$acp_module_id = $db->sql_nextid();
			$acp_stats_basic_basic = array('module_basename' => 'basic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_BASIC_BASIC',	'module_mode' => 'basic');
			add_module($acp_stats_basic_basic);
			$acp_stats_basic_advanced = array('module_basename' => 'basic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_BASIC_ADVANCED',	'module_mode' => 'advanced');
			add_module($acp_stats_basic_advanced);
			$acp_stats_basic_miscellaneous = array('module_basename' => 'basic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_BASIC_MISCELLANEOUS',	'module_mode' => 'miscellaneous');
			add_module($acp_stats_basic_miscellaneous);
			// Create STATS_ACTIVITY category + modules
			$acp_stats_activity = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_ACTIVITY');
			add_module($acp_stats_activity);
			$acp_module_id = $db->sql_nextid();
			$acp_stats_activity_forums = array('module_basename' => 'activity',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_ACTIVITY_FORUMS',	'module_mode' => 'forums');
			add_module($acp_stats_activity_forums);
			$acp_stats_activity_topics = array('module_basename' => 'activity',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_ACTIVITY_TOPICS',	'module_mode' => 'topics');
			add_module($acp_stats_activity_topics);
			$acp_stats_activity_users = array('module_basename' => 'activity',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_ACTIVITY_USERS',	'module_mode' => 'users');
			add_module($acp_stats_activity_users);
			// Create STATS_CONTRIBUTIONS category + modules
			$acp_stats_contributions = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_CONTRIBUTIONS');
			add_module($acp_stats_contributions);
			$acp_module_id = $db->sql_nextid();
			$acp_stats_contributions_attachments = array('module_basename' => 'contributions',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_CONTRIBUTIONS_ATTACHMENTS',	'module_mode' => 'attachments');
			add_module($acp_stats_contributions_attachments);
			$acp_stats_contributions_polls = array('module_basename' => 'contributions',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_CONTRIBUTIONS_POLLS',	'module_mode' => 'polls');
			add_module($acp_stats_contributions_polls);
			// Create STATS_PERIODIC category + modules
			$acp_stats_periodic = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_PERIODIC');
			add_module($acp_stats_periodic);
			$acp_module_id = $db->sql_nextid();
			$acp_stats_periodic_daily = array('module_basename' => 'periodic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_PERIODIC_DAILY',	'module_mode' => 'daily');
			add_module($acp_stats_periodic_daily);
			$acp_stats_periodic_monthly = array('module_basename' => 'periodic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_PERIODIC_MONTHLY',	'module_mode' => 'monthly');
			add_module($acp_stats_periodic_monthly);
			$acp_stats_periodic_hourly = array('module_basename' => 'periodic',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_PERIODIC_HOURLY',	'module_mode' => 'hourly');
			add_module($acp_stats_periodic_hourly);
			// Create STATS_SETTINGS category + modules
			$acp_stats_settings = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_SETTINGS');
			add_module($acp_stats_settings);
			$acp_module_id = $db->sql_nextid();
			$acp_stats_settings_board = array('module_basename' => 'settings',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_SETTINGS_BOARD',	'module_mode' => 'board');
			add_module($acp_stats_settings_board);
			$acp_stats_settings_profile = array('module_basename' => 'settings',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_SETTINGS_PROFILE',	'module_mode' => 'profile');
			add_module($acp_stats_settings_profile);
			// Create STATS_ADDONS category
			$acp_stats_addons = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => '0',	'module_class' => 'stats',	'module_langname'=> 'STATS_ADDONS');
			add_module($acp_stats_addons);
			$acp_module_id = $db->sql_nextid();
			// Add the scapegoat addon a.k.a. Miscellaneous
			$acp_stats_addons_miscellaneous = array('module_basename' => 'addons',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'stats',	'module_langname'=> 'STATS_ADDONS_MISCELLANEOUS', 'module_mode' => 'miscellaneous');
			add_module($acp_stats_addons_miscellaneous);
			
			// Now let's create the Control Panel in the ACP Mods-Tab
			// First select the id of the parent module (.Mods-Cat)
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . " WHERE module_langname = 'ACP_CAT_DOT_MODS'";
			$result = $db->sql_query($sql);
			$parent_id = (int) $db->sql_fetchfield('module_id');
			$db->sql_freeresult($result);
			
			$acp_stats = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $parent_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_STATS_INFO',	'module_mode' => '',	'module_auth' => '');
			add_module($acp_stats);
			$acp_module_id = $db->sql_nextid();
			set_stats_config('acp_parent_module', $acp_module_id);

			$acp_stats_settings = array('module_basename' => 'stats',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_STATS_GENERAL_INFO',	'module_mode' => 'settings',	'module_auth' => 'acl_a_board');
			add_module($acp_stats_settings);
			$acp_stats_addons = array('module_basename'	=> 'stats',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_STATS_ADDONS',	'module_mode' => 'addons',	'module_auth' => 'acl_a_board');
			add_module($acp_stats_addons);
		}
		
		/**
		* Parse the bbcode and smiley count
		* this might take a while and will create a lot of queries
		* the function overall_bbcode_smiley_count will do a loop that will prevent timeouts
		*/
		$url = $phpbb_root_path . '/install/index.' . $phpEx;
		$get_vars = 'mode=install&amp;sub=advanced';
		$continue_loop = overall_bbcode_smiley_count($start, $url, $get_vars);
		
		if($continue_loop == true)
		{
			$s_hidden_fields = '<input type="hidden" name="create" value="true" />';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";
			
			$template->assign_vars(array(
				'TITLE'		=> $user->lang['STAGE_ADVANCED'],
				'BODY'		=> $user->lang['STAGE_ADVANCED_IN_PROGRESS'],
				'S_HIDDEN'	=> $s_hidden_fields,
				'U_ACTION'	=> $url,
			));
		}
		else
		{
			$s_hidden_fields = '';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";

			$submit = $user->lang['NEXT_STEP'];
			
			set_stats_config('resync_stats_last_sync', time());

			$template->assign_vars(array(
				'TITLE'		=> $user->lang['STAGE_ADVANCED'],
				'BODY'		=> $user->lang['STAGE_ADVANCED_SUCCESSFUL'],
				'L_SUBMIT'	=> $submit,
				'S_HIDDEN'	=> $s_hidden_fields,
				'U_ACTION'	=> $url,
			));
		}	
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $portal_config_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
	);
}

?>