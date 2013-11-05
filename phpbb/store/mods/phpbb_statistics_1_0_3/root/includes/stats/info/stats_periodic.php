<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_periodic.php 171 2011-02-09 01:58:16Z marc1706 $
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
class stats_periodic_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_periodic',
		'title'		=> 'STATS_PERIODIC',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'daily'			=> array('title' => 'STATS_PERIODIC_DAILY', 'auth' => '', 'cat' => array('STATS_PERIODIC')),
			'monthly'		=> array('title' => 'STATS_PERIODIC_MONTHLY', 'auth' => '', 'cat' => array('STATS_PERIODIC')),
			'hourly'		=> array('title' => 'STATS_PERIODIC_HOURLY', 'auth' => '', 'cat' => array('STATS_PERIODIC')),
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