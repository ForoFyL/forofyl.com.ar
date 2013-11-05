<?php
/**
*
* @package language
* @copyright (c) 2009 kmklr72 with IP check code by mtrs and mail codes by ameeck's notify admin registration mod
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
* DO NOT CHANGE
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
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_DAP'								=> 'Double Account Preventer',
	'ACP_DAP_SETTINGS'						=> 'Double Account Preventer Settings',
	'ACP_DAP_SETTINGS_EXPLAIN'				=> 'Configure the various checks to prevent double accounts.',
	'ACP_DAP_PM_NOTIFICATION'				=> 'Double Account Preventer PM Notification',
	'ACP_DAP_PM_NOTIFICATION_EXPLAIN'		=> 'Configure the PM notifications.',
	'ACP_DAP_POST_NOTIFICATION'				=> 'Double Account Preventer Post Notification',
	'ACP_DAP_POST_NOTIFICATION_EXPLAIN'		=> 'Configure the post notifications.',
	'ACP_DAP_DUPE_USER_LIST'				=> 'Duplicate User List',
	'ACP_DAP_DUPE_USER_LIST_EXPLAIN'		=> 'List of all users marked as duplicate accounts.',
	'ACP_DAP_COOKIE_BAN_SETTINGS'			=> 'Cookie Ban Settings',
	'ACP_DAP_PROXY_SETTINGS'				=> 'Proxy Settings',

	'COOKIE_CHECK_REGISTRATION'				=> 'Cookie check at registration',
	'COOKIE_CHECK_REGISTRATION_EXPLAIN'		=> 'This searches for a cookie on the user’s browser. If it exists, the user is denied registration.',
	'COOKIE_CHECK_ENABLED'					=> 'Enabled',
	'COOKIE_CHECK_DISABLED'					=> 'Disabled',

	'DAP_EMAIL_NOTIFICATION'				=> 'Enable email notifications',
	'DAP_EMAIL_NOTIFICATION_EXPLAIN'		=> 'Send email notifications when a double account is detected.',
	'DAP_EMAIL_NOTIFICATION_ENABLED'		=> 'Enabled',
	'DAP_EMAIL_NOTIFICATION_DISABLED'		=> 'Disabled',
	'DAP_PM_NOTIFICATION'					=> 'Enable pm notifications',
	'DAP_PM_NOTIFICATION_EXPLAIN'			=> 'Send PM notifications when a double account is detected.',
	'DAP_PM_NOTIFICATION_ENABLED'			=> 'Enabled',
	'DAP_PM_NOTIFICATION_DISABLED'			=> 'Disabled',
	'DAP_PM_NOTIFICATION_SUBJECT'			=> 'PM notification subject',
	'DAP_PM_NOTIFICATION_SUBJECT_EXPLAIN'	=> 'Subject of Private Message notifications',
	'DAP_PM_NOTIFICATION_MESSAGE'			=> 'Message displayed in PM notifications',
	'DAP_PM_NOTIFICATION_MESSAGE_EXPLAIN'	=> 'Configure the PM notification message. The usable shortcodes are:<br /><br />[sitename]<br />[username]<br />[user_ip]<br />[user_email]<br />[user_regdate]<br />[user_detect_method]',
	'DAP_POST_NOTIFICATION'					=> 'Enable post notifications',
	'DAP_POST_NOTIFICATION_EXPLAIN'			=> 'Send post notifications when a double account is detected.',
	'DAP_POST_NOTIFICATION_ENABLED'			=> 'Enabled',
	'DAP_POST_NOTIFICATION_DISABLED'		=> 'Disabled',
	'DAP_POST_NOTIFICATION_FORUM'			=> 'Forum for post notifications',
	'DAP_POST_NOTIFICATION_FORUM_EXPLAIN'	=> 'ID numbers of the forums for post notifications. Separate with commas, for example: 1,2,3.',
	'DAP_POST_NOTIFICATION_SUBJECT'			=> 'Post notification subject',
	'DAP_POST_NOTIFICATION_SUBJECT_EXPLAIN'	=> 'Subject of post notifications',
	'DAP_POST_NOTIFICATION_MESSAGE'			=> 'Message displayed in post notifications',
	'DAP_POST_NOTIFICATION_MESSAGE_EXPLAIN'	=> 'Configure the post notification message. The usable shortcodes are:<br /><br />[sitename]<br />[username]<br />[user_ip]<br />[user_email]<br />[user_regdate]<br />[user_detect_method]',
	'DAP_ALERT_USER_ID'						=> 'Alert user ID',
	'DAP_ALERT_USER_ID_EXPLAIN'				=> 'User ID to use to send PM notifications. Only use one user ID.',
	'DAP_USERNAME'							=> 'Username',
	'DAP_DETECT_METHOD'						=> 'Detection Method',
	'DAP_COMMON_NAMES'						=> 'Common Names',
	'DAP_PROXY'								=> 'Proxy',
	'DAP_ACTIONS'							=> 'Actions',
	'DAP_NO_DUPE_USERS'						=> 'No duplicate users',
	'DAP_ADMIN'								=> 'Administrate user',
	'DAP_USER_COOKIE_BAN'					=> 'Ban by cookie',
	'DAP_COOKIE_BAN_ENABLED'				=> 'Enable Cookie Ban',
	'DAP_COOKIE_BAN_ENABLED_EXPLAIN'		=> 'Creates a cookie on user’s computer. If it exists, the user cannot view the forum.',
	'DAP_COOKIE_BAN_MESSAGE'				=> 'Cookie ban message',
	'DAP_COOKIE_BAN_MESSAGE_EXPLAIN'		=> 'Message displayed when user is banned by cookie',

	'DAP_PROXY_CHECK_ENABLED'				=> 'Enable Proxy Check',
	'DAP_PROXY_CHECK_ENABLED_EXPLAIN'		=> 'Checks for proxies and blacklisted IP addresses',
	'DAP_PROXY_CHECK_BLOCK'					=> 'Proxy Check Block',
	'DAP_PROXY_CHECK_BLOCK_EXPLAIN'			=> 'Block users using a proxy from registering on your forum',
	'DAP_PROXY_BLOCK_MESSAGE'				=> 'Proxy Block Message',
	'DAP_PROXY_BLOCK_MESSAGE_EXPLAIN'		=> 'Message displayed when blocked by proxy check',
));

/*
* This tidbit is from mtrs's Duplicate IP mod
*/
$lang = array_merge($lang, array(
	'IP_CHECK_REGISTRATION'					=> 'Duplicate IP check at registration',
	'IP_CHECK_REGISTRATION_EXPLAIN'			=> 'This can search same IP addreses at registration in database logs. <strong>Light</strong> option only searchs sessions and users tables, <strong>Full</strong> option scans all IP logged database tables, so takes longer.',
	'IP_CHECK_FULL'							=> 'Full scan',
	'IP_CHECK_DISABLE'						=> 'Disable Mod',
	'IP_CHECK_NONE'							=> 'No scan',
	'IP_CHECK_LIGHT'						=> 'Light scan',
));

// UMIL stuff
$lang = array_merge($lang, array(
	'INSTALL_DAP_MOD'						=> 'Install Double Account Preventer Mod',
	'INSTALL_DAP_MOD_CONFIRM'				=> 'Are you ready to install the Double Account Preventer Mod?',

	'ACP_CAT_DAP_MOD'						=> 'Double Account Preventer',

	'DAP_MOD'								=> 'Double Account Preventer',
	'DAP_MOD_EXPLAIN'						=> 'Prevents users from creating multiple accounts by using a cookie and checking their IP address.',
	'DONE'									=> 'Done!  Remove this file from your server.',

	'UNINSTALL_DAP_MOD'						=> 'Uninstall DAP Mod',
	'UNINSTALL_DAP_MOD_CONFIRM'				=> 'Are you ready to uninstall the Double Account Preventer Mod?  All settings and data saved by this mod will be removed!',
	'UPDATE_DAP_MOD'						=> 'Update DAP Mod',
	'UPDATE_DAP_MOD_CONFIRM'				=> 'Are you ready to update the Double Account Preventer Mod?',
));

?>