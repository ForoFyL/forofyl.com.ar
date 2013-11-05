<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_activity.php 171 2011-02-09 01:58:16Z marc1706 $
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
class stats_activity_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_activity',
		'title'		=> 'STATS_ACTIVITY',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'forums'		=> array('title' => 'STATS_ACTIVITY_FORUMS', 'auth' => '', 'cat' => array('STATS_ACTIVITY')),
			'topics'		=> array('title' => 'STATS_ACTIVITY_TOPICS', 'auth' => '', 'cat' => array('STATS_ACTIVITY')),
			'users'			=> array('title' => 'STATS_ACTIVITY_USERS', 'auth' => '', 'cat' => array('STATS_ACTIVITY')),
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