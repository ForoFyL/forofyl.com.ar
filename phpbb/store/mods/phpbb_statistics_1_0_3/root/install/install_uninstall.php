<?php
/**
*
* @package phpBB Statistics
* @version $Id: install_uninstall.php 119 2010-04-18 14:37:22Z marc1706 $
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
	if (!$this->installed_version)
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'uninstall',
		'module_title'		=> 'UNINSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 40,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'UNINSTALL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_uninstall extends module
{
	function install_uninstall(&$p_master)
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
					'TITLE'			=> $user->lang['UNINSTALL_INTRO'],
					'BODY'			=> $user->lang['UNINSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=uninstall",
				));

			break;

			case 'uninstall':
				$this->uninstall($mode, $sub);

			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function uninstall($mode, $sub)
	{
		global $db, $user, $template, $phpbb_root_path, $phpEx, $cache, $table_prefix;

		$this->page_title = $user->lang['STAGE_UNINSTALL'];
		$s_hidden_fields = '';
		
		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		// Drop the tables if existing
		stats_drop_table('phpbb_stats_config');
		stats_drop_table('phpbb_stats_smilies');
		stats_drop_table('phpbb_stats_bbcodes');
		// added in 1.0.2
		stats_drop_table('phpbb_stats_addons');
		
		//	Undo changes to profile_fields table
		switch($db_schema)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'mssql':
			case 'firebird':
				$sql = 'ALTER TABLE ' . $table_prefix . 'profile_fields DROP field_stats_show';
			break;
			
			case 'postgres':
			case 'sqlite':
			case 'oracle':
				$sql = 'ALTER TABLE ' . $table_prefix . 'profile_fields DROP COLUMN field_stats_show';
			break;
			
			default:
				trigger_error($user->lang['UNSUPPORTED_DB']);
			break;
		}
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		
		//	Start removing the modules
		$sql = 'SELECT module_id, module_class FROM ' . MODULES_TABLE . "
			WHERE module_langname = 'ACP_STATS_GENERAL_INFO'
				OR module_langname = 'ACP_ACTIVITY_USERS_INFO'
				OR module_langname = 'ACP_BASIC_MISCELLANEOUS_INFO'
				OR module_langname = 'ACP_BASIC_ADVANCED_INFO'
				OR module_langname = 'STATS_ACTIVITY_USERS'
				OR module_langname = 'STATS_ACTIVITY_TOPICS'
				OR module_langname = 'STATS_ACTIVITY_FORUMS'
				OR module_langname = 'STATS_SETTINGS_PROFILE'
				OR module_langname = 'STATS_SETTINGS_BOARD'
				OR module_langname = 'STATS_PERIODIC_HOURLY'
				OR module_langname = 'STATS_PERIODIC_MONTHLY'
				OR module_langname = 'STATS_PERIODIC_DAILY'
				OR module_langname = 'STATS_CONTRIBUTIONS_POLLS'
				OR module_langname = 'STATS_CONTRIBUTIONS_ATTACHMENTS'
				OR module_langname = 'STATS_BASIC_MISCELLANEOUS'
				OR module_langname = 'STATS_BASIC_ADVANCED'
				OR module_langname = 'STATS_BASIC_BASIC'
				OR module_langname = 'STATS_ADDONS_MISCELLANEOUS'
				OR module_langname = 'ACP_STATS_ADDONS'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			remove_module($row['module_id'], $row['module_class']);
		}
		$db->sql_freeresult($result);

		
		//	Clean the categories
		$cats = array('ACP_STATS_INFO', 'STATS_ACTIVITY', 'STATS_SETTINGS', 'STATS_PERIODIC', 'STATS_CONTRIBUTIONS', 'STATS_BASIC', 'STATS', 'STATS_ADDONS');
		
		foreach($cats as $current_cat)
		{
			$sql = 'SELECT right_id, module_id, module_class FROM ' . MODULES_TABLE . "
				WHERE module_langname = '" . $current_cat . "'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql = 'DELETE FROM ' . MODULES_TABLE . " WHERE module_id = '{$row['module_id']}'";
				$db->sql_query($sql);
				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET left_id = left_id - 2
					WHERE module_class = '{$row['module_class']}'
						AND left_id > {$row['right_id']}";
				$db->sql_query($sql);
			
				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET right_id = right_id - 2
					WHERE module_class = '{$row['module_class']}'
						AND right_id > {$row['right_id']}";
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);
		}
		
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . "
				WHERE auth_option = 'u_view_stats'
				";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . " WHERE auth_option_id = '{$row['auth_option_id']}'";
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
		
		// Now remove the auth_option from the database
		$sql = 'DELETE FROM ' . ACL_OPTIONS_TABLE . " WHERE auth_option = 'u_view_stats'";
		$db->sql_query($sql);
		
		$cache->purge();
				
		$template->assign_vars(array(
			'BODY'		=> $user->lang['UNINSTALL_CONGRATS'] . '<br /><br />' . $user->lang['UNINSTALL_CONGRATS_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['GOTO_INDEX'],
			'U_ACTION'	=> append_sid("{$phpbb_root_path}index.$phpEx"),
		));
	}
}

?>