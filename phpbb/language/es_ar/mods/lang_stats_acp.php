<?php
/**
*
* @package phpBB Statistics
* @version $Id: lang_stats_acp.php 167 2011-02-09 01:07:15Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: lang_portal_acp.php included in the Board3 Portal package (www.board3.de)
* @translator (c) ( Marc Alexander - http://www.m-a-styles.de )
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ACP_STATS_VERSION'							=> '<strong>phpBB Statistics v%s</strong>',
	// General
	'ACP_STATS_GENERAL_INFO' 					=> 'phpBB Statistics Administration',
	'ACP_STATS_GENERAL_INFO_EXPLAIN'			=> 'Thank you for choosing phpBB Statistics.',
	'ACP_STATS_GENERAL_SETTINGS' 				=> 'General Settings',
	'ACP_STATS_GENERAL_SETTINGS_EXPLAIN'		=> 'On this page, you can change settings which concern the whole Statistics MOD',
	'ACP_STATS_ENABLE'							=> 'Enable Statistics',
	'ACP_STATS_ENABLE_EXPLAIN'					=> 'Decide wether to enable the phpBB Statistics',
	'ACP_BASIC_BASIC_ENABLE'					=> 'Enable Basic Statistics',
	'ACP_BASIC_BASIC_ENABLE_EXPLAIN'			=> 'Choose if the Basic Statistics should be enabled',
	'ACP_BASIC_ADVANCED_ENABLE'					=> 'Enable Advanced Statistics',
	'ACP_BASIC_ADVANCED_ENABLE_EXPLAIN'			=> 'Choose if the Advanced Statistics should be enabled',
	'ACP_BASIC_MISCELLANEOUS_ENABLE'			=> 'Enable Miscellaneous Statistics',
	'ACP_BASIC_MISCELLANEOUS_ENABLE_EXPLAIN'	=> 'Choose if the Miscellaneous Statistics should be enabled',
	'ACP_ACTIVITY_FORUMS_ENABLE'				=> 'Enable Forums Activity Statistics',
	'ACP_ACTIVITY_FORUMS_ENABLE_EXPLAIN'		=> 'Choose if the Forums Activity Statistics should be enabled',
	'ACP_ACTIVITY_TOPICS_ENABLE'				=> 'Enable Topics Activity Statistics',
	'ACP_ACTIVITY_TOPICS_ENABLE_EXPLAIN'		=> 'Choose if the Topics Activity Statistics should be enabled',
	'ACP_ACTIVITY_USERS_ENABLE'					=> 'Enable Users Activity Statistics',
	'ACP_ACTIVITY_USERS_ENABLE_EXPLAIN'			=> 'Choose if the Users Activity Statistics should be enabled',
	'ACP_CONTRIBUTIONS_ATTACHMENTS_ENABLE' 		=> 'Enable Attachments Statistics',
	'ACP_CONTRIBUTIONS_ATTACHMENTS_ENABLE_EXPLAIN' => 'Choose if the Attachments Statistics should be enabled',
	'ACP_CONTRIBUTIONS_POLLS_ENABLE'			=> 'Enable Polls Statistics',
	'ACP_CONTRIBUTIONS_POLLS_ENABLE_EXPLAIN'	=> 'Choose if the Polls Statistics should be enabled',
	'ACP_PERIODIC_DAILY_ENABLE'					=> 'Enable Daily Statistics',
	'ACP_PERIODIC_DAILY_ENABLE_EXPLAIN'			=> 'Choose if the Daily Statistics should be enabled',
	'ACP_PERIODIC_MONTHLY_ENABLE'				=> 'Enable Monthly Statistics',
	'ACP_PERIODIC_MONTHLY_ENABLE_EXPLAIN'		=> 'Choose if the Monthly Statistics should be enabled',
	'ACP_PERIODIC_HOURLY_ENABLE'				=> 'Enable Hourly Statistics',
	'ACP_PERIODIC_HOURLY_ENABLE_EXPLAIN'		=> 'Choose if the Hourly Statistics should be enabled',
	'ACP_SETTINGS_BOARD_ENABLE'					=> 'Enable Board Settings Statistics',
	'ACP_SETTINGS_BOARD_ENABLE_EXPLAIN'			=> 'Choose if the Board Settings Statistics should be enabled',
	'ACP_SETTINGS_PROFILE_ENABLE'				=> 'Enable Profile Settings Statistics',
	'ACP_SETTINGS_PROFILE_ENABLE_EXPLAIN'		=> 'Choose if the Profile Settings Statistics should be enabled',
	'ACP_STATS_RESYNC_TIMEFRAME'				=> 'Resync Time',
	'ACP_STATS_RESYNC_TIMEFRAME_EXPLAIN'		=> 'Choose the days after which the cached Statistics should be refreshed. Setting this to 0 will automatically disable this feature.',
	
	// Advanced Stats
	'ACP_BASIC_ADVANCED_INFO'					=> 'Advanced Statistics',
	'ACP_BASIC_ADVANCED_INFO_EXPLAIN'			=> 'Here you can change the settings of the Advanced Statistics',
	'ACP_BASIC_ADVANCED_SETTINGS'				=> 'Advanced Statistics Settings',
	'ACP_BASIC_ADVANCED_SECURITY'				=> 'Enable secure Advanced Statistics',
	'ACP_BASIC_ADVANCED_SECURITY_EXPLAIN'		=> 'phpBB Version and Database info are not being displayed if enabled',
	'ACP_BASIC_ADVANCED_PRETEND'				=> 'Pretend to have newest phpBB Version installed',
	'ACP_BASIC_ADVANCED_PRETEND_EXPLAIN'		=> 'The Advanced Statistics will pretend to have the newester phpBB Version installed. <br /><strong>NOTE:</strong> This will only work, if secure Advanced Statistics are disabled. Also, if the ACP Version Check of phpBB does not work, this will not work either.',
	
	// Miscellaneous Stats
	'ACP_BASIC_MISCELLANEOUS_INFO'				=> 'Miscellaneous Statistics',
	'ACP_BASIC_MISCELLANEOUS_INFO_EXPLAIN'		=> 'Here you can change the settings of the Miscellaneous Statistics',
	'ACP_BASIC_MISCELLANEOUS_SETTINGS'			=> 'Miscellaneous Statistics Settings',
	'ACP_BASIC_MISCELLANEOUS_WARNINGS'			=> 'Hide Warnings Statistics',
	'ACP_BASIC_MISCELLANEOUS_WARNINGS_EXPLAIN'	=> 'If this is enabled the Warnings Statistics will not be displayed',
	'ACP_BASIC_MISCELLANEOUS_BBCODES'			=> 'Recount BBCodes and Smilies',
	'ACP_BASIC_MISCELLANEOUS_BBCODES_EXPLAIN'	=> 'Switch to yes if you added or modified custom bbcodes and if the counts have somehow been corrupted. This will be automatically turned of after the resync has finished',
	
	// Users Activity Stats
	'ACP_ACTIVITY_USERS_INFO'				=> 'Users Activity Statistics',
	'ACP_ACTIVITY_USERS_INFO_EXPLAIN'		=> 'Here you can change the settings of the Users Activity Statistics',
	'ACP_ACTIVITY_USERS_SETTINGS'			=> 'Users Activity Statistics Settings',
	'ACP_ACTIVITY_USERS_HIDE_ANONYMOUS'			=> 'Hide guests from Top XX Users Statistics',
	'ACP_ACTIVITY_USERS_HIDE_ANONYMOUS_EXPLAIN' => 'If this is enabled, guests will not be shown in the Top XX Users Statistics',
	
	// Add-Ons
	'INSTALLED_ADDONS'						=> 'Installed Add-Ons',
	'UNINSTALLED_ADDONS'					=> 'Uninstalled Add-Ons',

	));

?>