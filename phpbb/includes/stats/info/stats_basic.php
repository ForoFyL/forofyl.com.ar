<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_basic.php 171 2011-02-09 01:58:16Z marc1706 $
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
class stats_basic_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_basic',
		'title'		=> 'STATS_BASIC',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'basic'			=> array('title' => 'STATS_BASIC_BASIC', 'auth' => '', 'cat' => array('STATS_BASIC')),
			'advanced'		=> array('title' => 'STATS_BASIC_ADVANCED', 'auth' => '', 'cat' => array('STATS_BASIC')),
			'miscellaneous'	=> array('title' => 'STATS_BASIC_MISCELLANEOUS', 'auth' => '', 'cat' => array('STATS_BASIC')),
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