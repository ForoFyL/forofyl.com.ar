<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_addons.php 120 2010-04-20 12:18:45Z marc1706 $
* @copyright (c) 2009-2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Forum Statistics by TheUniqueTiger - Nayan Ghosh
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* stats_addons
* Add-on frontend
* This file parses all add-ons if selected
* If no add-on is selected the first add-on will be automatically selected
* If the selected add-on is disabled or if no add-on is installed you will see a nice looking error message
*/
class stats_addons
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $cache, $stats_addons_path, $id, $addon, $stats_config;
		
		if(sizeof($stats_config) < 1)
		{
			$stats_config = obtain_stats_config();
		}
		
		/**
		* new add-on system by Marc Alexander (c) 2010
		* enjoy ;)
		*/
		
		$module_url = append_sid("{$phpbb_root_path}stats.$phpEx");
		
		$do_first = $done_first = false;
		
		if(strlen($addon) < 1)
		{
			$do_first = true;
		}
		
		
		/**
		* load addons from stats_addons
		*/
		$sql = 'SELECT addon_classname, addon_enabled, addon_id
				FROM ' . STATS_ADDONS_TABLE . '
				GROUP BY addon_classname, addon_id, addon_enabled
				ORDER BY addon_id ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$classname = $row['addon_classname'];
			
			if (!class_exists($classname))
			{
				include("{$stats_addons_path}{$row['addon_classname']}.$phpEx");
			}
			if (!class_exists($classname))
			{
				trigger_error(sprintf($user->lang['CLASS_NOT_FOUND'], $classname, $row['addon_classname']), E_USER_ERROR);
			}
			
			$module = new $classname();
			
			/**
			* check if all needed vars exist
			* if not, output an error
			*/
			$error = '';
			if(!isset($module->module_name))
			{
				$error = 'module_name';
			}
			
			if(!isset($module->module_file))
			{
				$error = (strlen($error) > 0) ? $error . ', module_file' : 'module_file';
			}
			
			if(!isset($module->template_file))
			{
				$error = (strlen($error) > 0) ? $error . ', template_file' : 'template_file';
			}
			
			if(strlen($error) > 0)
			{
				trigger_error(sprintf($user->lang['DAMAGED_ADDON'], $classname, $error));
			}
			
			/**
			* start loading the necessary data
			*/
			if(strlen($module->template_file) > 0)
			{
				$user->add_lang('mods/stats_addons/' . $module->template_file);
			}
			
			/**
			* run load_stats() if the addon is selected
			* if no addon is selected, run the first one
			*/
			if(($addon == $module->module_file && $row['addon_enabled']) || ($do_first == true && $done_first == false))
			{
				$module->load_stats();
				$done_first = true;
				$addon = $module->module_file;
			}
			
			/**
			* modify the block_vars of t_block2 so the enabled add-ons show up
			*/
			if($row['addon_enabled'] == true)
			{
				$template->assign_block_vars('t_block2', array(
					'L_TITLE' 		=> $user->lang[$module->module_name],
					'S_SELECTED'	=> ($addon == $module->module_file) ? true : false,
					'U_TITLE'		=> (strpos($module_url, '?')) ? $module_url . '&amp;i=' . $id . '&amp;mode=miscellaneous&amp;addon=' . $module->module_file : $module_url . '?i=' . $id . '&amp;mode=miscellaneous&amp;addon=' . $module->module_file,
				));
			}
			
			$addons[$module->module_file] = $row;
		}			
		$db->sql_freeresult($result);
		
		/**
		* Check if we need to install the add-on
		* only users with admin permissions should be able to do this
		*/
		if($done_first == false && strlen($addon) > 0 && !isset($addons[$addon]) && $auth->acl_get('a_'))
		{
			if (!class_exists($addon))
			{
				include("{$stats_addons_path}{$addon}.$phpEx");
			}
			if (!class_exists($addon))
			{
				trigger_error(sprintf($user->lang['CLASS_NOT_FOUND'], $addon, $addon), E_USER_ERROR);
			}
			
			$module = new $addon();
			
			$module->install();
			
			redirect($module_url . '?i=' . $id . '&amp;mode=miscellaneous&amp;addon=' . $module->module_file);
		}
		
		/**
		* Check if no add-on is installed or if the selected add-on is disabled
		*/
		if($done_first == false && !isset($addons[$addon]))
		{
			$template->assign_var('ADDON_INFO', $user->lang['NO_ADDONS']);
			$addon = 'no_addons_title';
			$template->assign_block_vars('t_block2', array(
				'L_TITLE' 		=> $user->lang['NO_ADDONS_TITLE'],
				'S_SELECTED'	=> true,
				'U_TITLE'		=> (strpos($module_url, '?')) ? $module_url . '&amp;i=' . $id . '&amp;mode=miscellaneous' : $module_url . '?i=' . $id . '&amp;mode=miscellaneous',
			));
		}
		elseif($done_first == false && $addons[$addon]['addon_enabled'] == false)
		{
			$template->assign_var('ADDON_INFO', $user->lang['ADDON_DISABLED']);
			$addon = 'addon_disabled_title';
		}
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang[strtoupper($addon)],			
			'S_STATS_ACTION'	=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = 'stats/addons/' . (($addon == 'no_addons_title' || $addon == 'addon_disabled_title') ? 'no_addon' : $addon);
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($addon)];
		
	}
}
?>