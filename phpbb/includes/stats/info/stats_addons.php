<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_addons.php 171 2011-02-09 01:58:16Z marc1706 $
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
class stats_addons_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_addons',
		'title'		=> 'STATS_ADDONS',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'miscellaneous'			=> array('title' => 'STATS_ADDONS_MISCELLANEOUS', 'auth' => '', 'cat' => array('STATS_ADDONS')),
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