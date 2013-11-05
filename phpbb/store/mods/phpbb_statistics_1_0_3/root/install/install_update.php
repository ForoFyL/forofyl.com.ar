<?php
/**
*
* @package phpBB Statistics
* @version $Id: install_update.php 167 2011-02-09 01:07:15Z marc1706 $
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
	if (!$this->installed_version || $this->installed_version == NEW_PHPBB_STATS_VERSION)
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'update',
		'module_title'		=> 'UPDATE',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 20,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'UPDATE_DB', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_update extends module
{
	function install_update(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $cache, $phpEx;

		$stats_version = load_stats_config();

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['UPDATE_INSTALLATION'],
					'BODY'			=> $user->lang['UPDATE_INSTALLATION_EXPLAIN'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements",
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'update_db':
				$this->update_db_schema($mode, $sub);

			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				set_stats_config('stats_version', NEW_PHPBB_STATS_VERSION);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['UPDATE_CONGRATS_EXPLAIN'], NEW_PHPBB_STATS_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_STATS'],
					'U_ACTION'	=> append_sid($phpbb_root_path . 'stats.' . $phpEx),
				));


			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$passed = array('files' => false,);

		// Check whether all old files are deleted
		include($phpbb_root_path . 'install/outdated_files.' . $phpEx);
		
		umask(0);

		$passed['files'] = true;
		foreach ($oudated_files as $file)
		{
			if (@file_exists($phpbb_root_path . $file))
			{
				if ($passed['files'])
				{
					$template->assign_block_vars('checks', array(
						'S_LEGEND'			=> true,
						'LEGEND'			=> $user->lang['FILES_OUTDATED'],
						'LEGEND_EXPLAIN'	=> $user->lang['FILES_OUTDATED_EXPLAIN'],
					));
				}
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $file,
					'RESULT'	=> '<strong style="color:red">' . $user->lang['FILES_EXISTS'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
				$passed['files'] = false;
			}
		}
		$did['changed'] = true;

		$url = (!in_array(false, $passed)) ? $this->p_master->module_url . "?mode=$mode&amp;sub=update_db" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
		$submit = (!in_array(false, $passed)) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];
		$body = (!in_array(false, $passed)) ? $user->lang['NOT_REQUIREMENTS_EXPLAIN'] : $user->lang['REQUIREMENTS_EXPLAIN'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $body,
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Add some Tables, Columns and Index to the database-schema
	*/
	function update_db_schema($mode, $sub)
	{
		global $db, $user, $template, $stats_version, $table_prefix, $phpbb_root_path, $phpEx, $cache;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$dbms_data = get_dbms_infos();
		
		$sql_query = '';
		
		$stats_version = load_stats_config();
		$this->page_title = $user->lang['STAGE_UPDATE_DB'];
		$reparse_modules = true; // this will trigger reparsing the bbcode and smiley count
	
		// 	Create the tables
		switch($stats_version['stats_version'])
		{
			case '0.1.0':
			case '0.1.1':
			case '0.2.0':
			case '1.0.0':
				// Make sure we don't try to create a table if it's already there
				stats_drop_table('phpbb_stats_smilies');
				stats_drop_table('phpbb_stats_bbcodes');
				// added in 1.0.2
				stats_drop_table('phpbb_stats_addons');
				switch($dbms_data['db_schema'])
				{
					case 'mysql_40':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname varchar(255) DEFAULT '' NOT NULL,
										addon_enabled tinyint DEFAULT '0' NOT NULL,
										addon_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (addon_classname)
									);
									CREATE TABLE phpbb_stats_smilies (
										smiley_url varchar(255) DEFAULT '' NOT NULL,
										smiley_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (smiley_url)
									);

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode varchar(255) DEFAULT '' NOT NULL,
										bbcode_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (bbcode)
									);";
					break;
					
					case 'mysql_41':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname varchar(255) DEFAULT '' NOT NULL,
										addon_enabled tinyint NOT NULL,
										addon_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (addon_classname)
									) CHARACTER SET utf8 COLLATE utf8_bin;

									CREATE TABLE phpbb_stats_smilies (
										smiley_url varchar(255) DEFAULT '' NOT NULL,
										smiley_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (smiley_url)
									) CHARACTER SET utf8 COLLATE utf8_bin;

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode varchar(255) DEFAULT '' NOT NULL,
										bbcode_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
										PRIMARY KEY (bbcode)
									) CHARACTER SET utf8 COLLATE utf8_bin;";
					break;
					
					case 'mssql':
						$sql_query = "CREATE TABLE [phpbb_stats_addons] (
										[addon_classname] [varchar] (255) DEFAULT ('') NOT NULL ,
										[addon_enabled] [tinyint] DEFAULT (0) NOT NULL ,
										[addon_id] [int] DEFAULT (0) NOT NULL
									) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
									GO

									ALTER TABLE [phpbb_stats_addons] WITH NOCHECK ADD 
										CONSTRAINT [PK_phpbb_stats_addons] PRIMARY KEY  CLUSTERED 
										(
											[addon_classname]
										)  ON [PRIMARY] 
									GO

									CREATE TABLE [phpbb_stats_smilies] (
										[smiley_url] [varchar] (255) DEFAULT ('') NOT NULL ,
										[smiley_count] [int] DEFAULT (0) NOT NULL
									) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
									GO

									ALTER TABLE [phpbb_stats_smilies] WITH NOCHECK ADD 
										CONSTRAINT [PK_phpbb_stats_smilies] PRIMARY KEY  CLUSTERED 
										(
											[smiley_url]
										)  ON [PRIMARY] 
									GO

									CREATE TABLE [phpbb_stats_bbcodes] (
										[bbcode] [varchar] (255) DEFAULT ('') NOT NULL ,
										[bbcode_count] [int] DEFAULT (0) NOT NULL
									) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
									GO

									ALTER TABLE [phpbb_stats_bbcodes] WITH NOCHECK ADD 
										CONSTRAINT [PK_phpbb_stats_bbcodes] PRIMARY KEY  CLUSTERED 
										(
											[bbcode]
										)  ON [PRIMARY] 
									GO";
					break;
					
					case 'postgres':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname varchar(255) DEFAULT '' NOT NULL,
										addon_enabled smallint DEFAULT '' NOT NULL,
										addon_id INT4 DEFAULT '0' NOT NULL,
										PRIMARY KEY (config_name)
									);

									CREATE TABLE phpbb_stats_smilies (
										smiley_url varchar(255) DEFAULT '' NOT NULL,
										smiley_count INT4 DEFAULT '0' NOT NULL,
										PRIMARY KEY (smiley_url)
									);

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode varchar(255) DEFAULT '' NOT NULL,
										bbcode_count INT4 DEFAULT '0' NOT NULL,
										PRIMARY KEY (bbcode)
									);";
					break;
					
					case 'sqlite':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname varchar(255) NOT NULL DEFAULT '',
										addon_enabled tinyint(1) NOT NULL DEFAULT 0,
										addon_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
										PRIMARY KEY (config_name)
									);

									CREATE TABLE phpbb_stats_smilies (
										smiley_url varchar(255) NOT NULL DEFAULT '',
										smiley_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
										PRIMARY KEY (smiley_url)
									);

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode varchar(255) NOT NULL DEFAULT '',
										bbcode_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
										PRIMARY KEY (bbcode)
									);";
					break;
					
					case 'firebird':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
										addon_enabled SMALLINT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
										addon_id INTEGER DEFAULT 0 NOT NULL,
									);;

									ALTER TABLE phpbb_stats_config ADD PRIMARY KEY (config_name);;

									CREATE TABLE phpbb_stats_smilies (
										smiley_url VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
										smiley_count INTEGER DEFAULT 0 NOT NULL
									);;

									ALTER TABLE phpbb_stats_smilies ADD PRIMARY KEY (smiley_url);;

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
										bbcode_count INTEGER DEFAULT 0 NOT NULL
									);;

									ALTER TABLE phpbb_stats_bbcodes ADD PRIMARY KEY (bbcode);;";
					break;
					
					case 'oracle':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
										addon_classname varchar2(255) DEFAULT '' ,
										addon_enabled number(5) DEFAULT 0 ,
										addon_id number(8) DEFAULT '0' NOT NULL,
										CONSTRAINT pk_phpbb_stats_addons PRIMARY KEY (addon_classname)
									)
									/
							
									CREATE TABLE phpbb_stats_smilies (
										smiley_url varchar2(255) DEFAULT '' ,
										smiley_count number(8) DEFAULT '0' NOT NULL,
										CONSTRAINT pk_phpbb_stats_smilies PRIMARY KEY (smiley_url)
									)
									/

									CREATE TABLE phpbb_stats_bbcodes (
										bbcode varchar2(255) DEFAULT '' ,
										bbcode_count number(8) DEFAULT '0' NOT NULL,
										CONSTRAINT pk_phpbb_stats_bbcodes PRIMARY KEY (bbcode)
									)
									/";
					break;
					
					default:
						trigger_error($user->lang['UNSUPPORTED_DB']);
					break;
				}
			case '1.0.1':
				// Make sure we don't try to create a table if it's already there
				stats_drop_table('phpbb_stats_addons');
				switch($dbms_data['db_schema'])
				{
					case 'mysql_40':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname varchar(255) DEFAULT '' NOT NULL,
									addon_enabled tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
									addon_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
									PRIMARY KEY (addon_classname)
								);";
					break;
					
					case 'mysql_41':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname varchar(255) DEFAULT '' NOT NULL,
									addon_enabled mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
									addon_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
									PRIMARY KEY (addon_classname)
								) CHARACTER SET utf8 COLLATE utf8_bin;";
					break;
					
					case 'mssql':
						$sql_query = "CREATE TABLE [phpbb_stats_addons] (
									[addon_classname] [varchar] (255) DEFAULT ('') NOT NULL ,
									[addon_enabled] [int] DEFAULT (0) NOT NULL ,
									[addon_id] [int] DEFAULT (0) NOT NULL
								) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
								GO

								ALTER TABLE [phpbb_stats_addons] WITH NOCHECK ADD 
									CONSTRAINT [PK_phpbb_stats_addons] PRIMARY KEY  CLUSTERED 
									(
										[addon_classname]
									)  ON [PRIMARY] 
								GO";
					break;
					
					case 'postgres':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname varchar(255) DEFAULT '' NOT NULL,
									addon_enabled INT4 DEFAULT '0' NOT NULL,
									addon_id INT4 DEFAULT '0' NOT NULL,
									PRIMARY KEY (addon_classname)
								);";
					break;
					
					case 'sqlite':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname varchar(255) NOT NULL DEFAULT '',
									addon_enabled INTEGER UNSIGNED NOT NULL DEFAULT '0',
									addon_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
									PRIMARY KEY (addon_classname)
								);";
					break;
					
					case 'firebird':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
									addon_enabled INTEGER DEFAULT 0 NOT NULL,
									addon_id INTEGER DEFAULT 0 NOT NULL,
								);;

								ALTER TABLE phpbb_stats_addons ADD PRIMARY KEY (addon_classname);;";
					break;
					
					case 'oracle':
						$sql_query = "CREATE TABLE phpbb_stats_addons (
									addon_classname varchar2(255) DEFAULT '' ,
									addon_enabled number(5) DEFAULT 0 ,
									addon_id number(8) DEFAULT '0' NOT NULL,
									CONSTRAINT pk_phpbb_stats_addons PRIMARY KEY (addon_classname)
								)
								/";
					break;
					
					default:
						trigger_error($user->lang['UNSUPPORTED_DB']);
					break;
				}
			break;
			
			case '1.0.2':
				$sql_query = '';
				set_stats_config('resync_stats', '1');
				set_stats_config('resync_stats_last_sync', '1');
			break;
			
			default:
				trigger_error($user->lang['UNSUPPORTED_VERSION']);
			break;
		}
		
		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
		$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));
		$sql_query = split_sql_file($sql_query, $dbms_data['delimiter']);
		// make the new one's
		foreach ($sql_query as $sql)
		{
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);
		
		switch ($this->installed_version)
		{
			case '0.1.0':
			case '0.1.1':
			case '0.2.0':
			case '1.0.0':
				$sql = 'DELETE FROM ' . STATS_CONFIG_TABLE . "
						WHERE config_name = 'authorized_group'";
				$db->sql_query($sql);
			case '1.0.1':
				
			break;
		}
		
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
		
		$stats_config = load_stats_config();
		
		// Get group_ids of authorized groups
		if(isset($stats_config['authorized_group']))
		{
			$group_id = (strpos($stats_config['authorized_group'], ',') !== FALSE) ? explode(',', $stats_config['authorized_group']) : array($stats_config['authorized_group']);

			foreach($group_id as $current_group)
			{
				$sql_ary[] = array(
						'group_id'        => (int) $current_group,
						'auth_option_id'    => (int) $auth_option_id,
						'auth_setting'        => 1,
				);
			}
			$db->sql_multi_insert(ACL_GROUPS_TABLE, $sql_ary);
			$cache->destroy('acl_options');
		}
		
		if ($reparse_modules)
		{
			$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced&amp;create=1";
		}
		else
		{
			$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";
		}
	
		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_CREATE_TABLE'],
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $stats_version, $phpEx, $db, $phpbb_root_path;
		$stats_version = load_stats_config();
		
		$create = request_var('create', '');
		if ($create)
		{	
			// Make sure we don't do this on every reload of the page
			$modules_updated = request_var('modules_updated', 0);
			if($modules_updated != true)
			{
				// Remove the ACP add-ons we don't need anymore in 1.0.2 (pretty much all of them ;) )
				$remove_modules = array(
					'ACP_STATS_GENERAL_INFO',
					'ACP_BASIC_ADVANCED_INFO',
					'ACP_BASIC_MISCELLANEOUS_INFO',
					'ACP_ACTIVITY_USERS_INFO',
				);
				$sql = 'SELECT module_id, module_class FROM ' . MODULES_TABLE . '
						WHERE ' . $db->sql_in_set('module_langname', $remove_modules);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					remove_module($row['module_id'], $row['module_class']);
				}
				
				// Add modules
				if(!isset($stats_version['acp_parent_module']) || $stats_version['acp_parent_module'] < 1)
				{
					$sql = 'SELECT module_id AS id
							FROM ' . MODULES_TABLE . "
							WHERE module_langname = 'ACP_STATS_INFO'";
					$result = $db->sql_query($sql);
					$choosen_acp_module = $db->sql_fetchfield('id');
					$db->sql_freeresult($result);
				}
				else
				{
					$choosen_acp_module = $stats_version['acp_parent_module'];
				}
				
				$acp_stats_settings = array('module_basename' => 'stats',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname' => 'ACP_STATS_GENERAL_INFO',	'module_mode' => 'settings',	'module_auth' => 'acl_a_board');
				add_module($acp_stats_settings);
				$acp_stats_addons = array('module_basename'	=> 'stats',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname' => 'ACP_STATS_ADDONS',	'module_mode' => 'addons',	'module_auth' => 'acl_a_board');
				add_module($acp_stats_addons);
			}
			
			
			//	Reparse the bbcode and smiley count
			$start = request_var('start_sql', 0);
			$url = $phpbb_root_path . '/install/index.' . $phpEx;
			$get_vars = 'mode=update&amp;sub=advanced&amp;modules_updated=1&amp;create=true';
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
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $stats_version_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
	);
}

?>