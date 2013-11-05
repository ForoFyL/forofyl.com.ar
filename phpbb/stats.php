<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats.php 162 2010-12-11 13:29:18Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Forum Statistics by TheUniqueTiger - Nayan Ghosh
*/


/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_STATS_MOD', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$stats_addons_path = $phpbb_root_path . '/statistics/addons/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);
include($phpbb_root_path . 'statistics/includes/functions.'.$phpEx);
if(!function_exists('update_last_username'))
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
}

//start initial vars setup
$id = request_var('i', '');
$mode = request_var('mode', '');
$addon = request_var('addon', '');

// Start session
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/stats');

if(!isset($stats_config))
{
	$stats_config = obtain_stats_config();
}

/* 	check if the user has permissions to view the stats
*	the whole permission set is no phpBB-based
*/
if (!$auth->acl_get('u_view_stats'))
{
	trigger_error('NOT_AUTHORISED');
}

if (!$stats_config['stats_enable'])
{
	trigger_error('STATS_NOT_ENABLED');
}

/**
* tell the template which menu-tab it shouldn't load
*/
$template->assign_var('STATS_ADDON_DONT_LOAD', $user->lang['STATS_ADDONS_MISCELLANEOUS']);
$template->assign_var('STATS_ADDONS_LANG', $user->lang['STATS_ADDONS']);

$module = new p_master();

// Instantiate module system and generate list of available modules
$module->list_modules('stats');

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid("{$phpbb_root_path}stats.$phpEx"));

// Generate the page, do not display/query online list
$module->display($module->get_page_title(), false);

?>