<?php
/**
*
* @package acp
* @version $Id: phpbb_stats_check_version.php 49 2009-07-09 10:11:46Z marc1706 $
* @copyright (c) 2007 StarTrekGuide
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package mod_version_check
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_stats_check_version
{
	function version()
	{
		global $stats_config, $phpbb_root_path, $phpEx;
			if (!function_exists('obtain_stats_config'))
			{
				include($phpbb_root_path . 'statistics/includes/functions.' . $phpEx);
			}
			$stats_config = obtain_stats_config();

		return array(
			'author'	=> 'marc1706',
			'title'		=> 'phpBB Statistics',
			'tag'		=> 'phpbb_stats',
			'version'	=> $stats_config['stats_version'],
			'file'		=> array('m-a-styles.de', 'updatecheck', 'phpbb_stats.xml'),
		);
	}
}

?>