<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_contributions.php 171 2011-02-09 01:58:16Z marc1706 $
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
class stats_contributions_info
{
	function module()
	{
		return array(
		'filename'	=> 'stats_contributions',
		'title'		=> 'STATS_CONTRIBUTIONS',
		'version'	=> '1.0.3',
		'modes'		=> array(
			'attachments'			=> array('title' => 'STATS_CONTRIBUTIONS_ATTACHMENTS', 'auth' => '', 'cat' => array('STATS_CONTRIBUTIONS')),
			'polls'		=> array('title' => 'STATS_CONTRIBUTIONS_POLLS', 'auth' => '', 'cat' => array('STATS_CONTRIBUTIONS')),
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