<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_settings.php 171 2011-02-09 01:58:16Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
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
* @package module_install
*/
class stats_settings_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_settings',
		'title'		=> 'STATS_SETTINGS',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'board'			=> array('title' => 'STATS_SETTINGS_BOARD', 'auth' => '', 'cat' => array('STATS_SETTINGS')),
			'profile'		=> array('title' => 'STATS_SETTINGS_PROFILE', 'auth' => '', 'cat' => array('STATS_SETTINGS')),
		),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
?>