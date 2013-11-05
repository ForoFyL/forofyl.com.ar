<?php
/**
*
* @package phpBB Statistics
* @version $Id: outdated_files.php 140 2010-05-07 14:22:04Z marc1706 $
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

$oudated_files = array(
	'language/en/mods/lang_stats_acp_logs.php',
	'language/en/mods/stats_addons.php',
	'language/de/mods/stats_addons.php',
	'language/de/mods/lang_stats_acp_logs.php',
	'statistics/includes/functions_addons.php',
	'styles/prosilver/template/stats/addon_miscellaneous.html',
	'styles/subsilver2/template/stats/addon_miscellaneous.html',
);

?>