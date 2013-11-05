<?php
/**
*
* @package acp
* @version $Id: info_mcp_announce.php 126 2008-10-12 22:10:09Z lefty74 $ 
* @copyright (c) 2007 lefty74 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge language entries into the common lang array
$lang = array_merge($lang, array(
	'MCP_ANNOUNCEMENTS_CENTRE'			=> 'Announcement Centre',
	'MCP_ANNOUNCE_FRONT'				=> 'Front page',
	'LOG_ANNOUNCEMENT_UPDATED'			=> '<strong>Announcement(s) updated</strong>',
	'LOG_ANNOUNCEMENT_CONFIG_UPDATED'	=> '<strong>Announcement Configuration updated</strong>',
));
?>