<?php

/**
*
* @package - mChat
* @version $Id: info_acp_mchat.php 1.3.2 2009-10-20 06:10:00 EST rmcgirr83 $
* @copyright (c) 2009 phpbb3bbcodes.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
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
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters for use
// ’ » “ ” …


$lang = array_merge($lang, array(

	// UMIL stuff
	'ACP_MCHAT_CONFIG'				=> 'Configuration',
	'ACP_CAT_MCHAT'					=> 'mChat',
	'ACP_MCHAT_TITLE'				=> 'Mini-Chat',
	'ACP_MCHAT_TITLE_EXPLAIN'		=> 'A mini chat (aka “shout box”) for your forum',
	'MCHAT_TABLE_DELETED'			=> 'The mChat table was successfully deleted',
	'MCHAT_TABLE_CREATED'			=> 'The mChat table was successfully created',

	// ACP entries
	'LOG_MCHAT_CONFIG_UPDATE'		=> '<strong>Updated mChat config </strong>',
	'MCHAT_CONFIG_SAVED'			=> 'Mini Chat configuration has been updated',
	'MCHAT_TITLE'					=> 'Mini-Chat',
	'MCHAT_VERSION'					=> 'Version:',
	'MCHAT_ENABLE'					=> 'Enable mChat MOD',
	'MCHAT_ENABLE_EXPLAIN'			=> 'Enable or disable the mod globally.',
	'MCHAT_ON_INDEX'				=> 'mChat On Index',
	'MCHAT_ON_INDEX_EXPLAIN'		=> 'Allow the display of the mChat on the index page.',	
	'MCHAT_LOCATION'				=> 'Location on Forum',
	'MCHAT_LOCATION_EXPLAIN'		=> 'Choose the location of the mChat on the index page.',
	'MCHAT_TOP_OF_FORUM'			=> 'Top of Forum',
	'MCHAT_BOTTOM_OF_FORUM'			=> 'Bottom of Forum',
	'MCHAT_REFRESH'					=> 'Refresh',
	'MCHAT_REFRESH_EXPLAIN'			=> 'Number of seconds before chat automatically refreshes. <strong>Do not go below 5 seconds</strong>.',
	'MCHAT_PRUNE'					=> 'Enable Prune',
	'MCHAT_PRUNE_EXPLAIN'			=> 'Set to yes to enable the prune feature.<br /><em>Only occurs if a user views the custom or archive pages</em>.',
	'MCHAT_PRUNE_NUM'				=> 'Prune Number',
	'MCHAT_PRUNE_NUM_EXPLAIN'		=> 'The number of messages to retain in the chat.',	
	'MCHAT_MESSAGE_LIMIT'			=> 'Message limit',
	'MCHAT_MESSAGE_LIMIT_EXPLAIN'	=> 'The maximum number of messages to show on the main page of the forum.<br /><em>Recommended from 10 to 20</em>.',
	'MCHAT_ARCHIVE_LIMIT'			=> 'Archive limit',
	'MCHAT_ARCHIVE_LIMIT_EXPLAIN'	=> 'The maximum number of messages to show per page on the archive page.<br /> <em>Recommended from 25 to 50</em>.',
	'MCHAT_FLOOD_TIME'				=> 'Flood time',
	'MCHAT_FLOOD_TIME_EXPLAIN'		=> 'The number of seconds a user must wait before posting another message in the chat.<br /><em>Recommended 5 to 30, set to 0 to disable</em>.',
	'MCHAT_MAX_MESSAGE_LENGTH'			=> 'Max message length',
	'MCHAT_MAX_MESSAGE_LENGTH_EXPLAIN'	=> 'Max number of characters allowed per message posted.<br /><em>Recommended from 100 to 500, set to 0 to disable</em>.',
	'MCHAT_CUSTOM_PAGE'				=> 'Custom Page',
	'MCHAT_CUSTOM_PAGE_EXPLAIN'		=> 'Allow the use of the custom page',
	'MCHAT_DATE_FORMAT'				=> 'Date format',
	'MCHAT_DATE_FORMAT_EXPLAIN'		=> 'The syntax used is identical to the PHP <a href="http://www.php.net/date">date()</a> function.',
	'MCHAT_CUSTOM_DATEFORMAT'		=> 'Custom…',
	'MCHAT_WHOIS'					=> 'Whois',
	'MCHAT_WHOIS_EXPLAIN'			=> 'Allow a display of users viewing the custom page',
	'MCHAT_WHOIS_REFRESH'			=> 'Whois refresh',
	'MCHAT_WHOIS_REFRESH_EXPLAIN'	=> 'Number of seconds before whois stats refreshes.<br /><strong>Do not go below 30 seconds</strong>.',
	'MCHAT_BBCODES_DISALLOWED'		=> 'Disallowed bbcodes',
	'MCHAT_BBCODES_DISALLOWED_EXPLAIN'	=> 'Here you can input the types of bbcode that are <strong>not</strong> to be used in a message.<br />Separate bbcodes with a vertical bar, for example: b|u|code',
	
	// error reporting
	'WARNING'					=> 'Warning',
	'TOO_LONG_DATE'		=> 'The date format you entered is too long.',
	'TOO_SHORT_DATE'		=> 'The date format you entered is too short.',
	'TOO_SMALL_REFRESH'	=> 'The refresh value is too small.',
	'TOO_LARGE_REFRESH'	=> 'The refresh value is too large.',
	'TOO_SMALL_MESSAGE_LIMIT'	=> 'The message limit value is too small.',
	'TOO_LARGE_MESSAGE_LIMIT'	=> 'The message limit value is too large.',
	'TOO_SMALL_ARCHIVE_LIMIT'	=> 'The archive limit value is too small.',
	'TOO_LARGE_ARCHIVE_LIMIT'	=> 'The archive limit value is too large.',
	'TOO_SMALL_FLOOD_TIME'	=> 'The flood time value is too small.',
	'TOO_LARGE_FLOOD_TIME'	=> 'The flood time value is too large.',
	'TOO_SMALL_MAX_MESSAGE_LNGTH'	=> 'The max message length value is too small.',
	'TOO_LARGE_MAX_MESSAGE_LNGTH'	=> 'The max message length value is too large.',
	'TOO_SMALL_MAX_WORDS_LNGTH'		=> 'The max words length value is too small.',
	'TOO_LARGE_MAX_WORDS_LNGTH'		=> 'The max words length value is too large.',	
	
));

?>