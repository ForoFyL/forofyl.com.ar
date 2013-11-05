<?php
/**
*
* @package - mChat
* @version 1.3.5 07.10.2009
* @copyright (c) djs596 ( http://djs596.com/ ), (c) RMcGirr83 ( http://www.rmcgirr83.org/ ), (c) Stokerpiller ( http://www.phpbb3bbcodes.com/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE!
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

	// MCHAT
	'MCHAT_ADD'					=> 'Send',
	'MCHAT_ARCHIVE'				=> 'Archive',	
	'MCHAT_ARCHIVE_PAGE'		=> 'Mini-Chat Archive',
	'MCHAT_ARCHIVENOMESSAGE'	=> 'There are no messages here',	
	'MCHAT_AUTOUPDATE'			=> 'Autoupdate every <strong>%d</strong> seconds',
	'MCHAT_BBCODES'				=> 'BBCodes',
	'MCHAT_CLEAN'				=> 'X',
	'MCHAT_CLEANED'				=> 'All messages have been successfully removed',
	'MCHAT_COPYRIGHT_CHECK'		=> '&copy; <a href="http://www.phpbb3bbcodes.com/">phpBB3BBCodes.com</a>',
	'MCHAT_DELALLMESS'			=> 'Remove all messages?',
	'MCHAT_DELCONFIRM'			=> 'Do you confirm removal?',
	'MCHAT_DELITE'				=> 'Delete',
	'MCHAT_EDIT'				=> 'Edit',
	'MCHAT_EDITINFO'			=> 'Edit the message and click OK',
	'MCHAT_ENABLE'				=> 'Sorry, the Mini-Chat is currently unavailable',	
	'MCHAT_ERROR'				=> 'Error',	
	'MCHAT_FLOOD'				=> 'You can not post another message so soon after your last',	
	'MCHAT_HELP'				=> '?',
	'MCHAT_HELP_INFO'			=> 'Rules: \n1. No swearing\n2. Don’t advertise your site\n3. Don’t leave several messages in succession\n4. Don’t leave a pointless message\n5. Don’t leave a message consisting of only smilies',	// \n signifies a new line //
	'MCHAT_IP'					=> 'IP:',
	'MCHAT_IP_WHOIS_FOR'		=> 'IP whois for %s',	
	'MCHAT_LOAD'				=> 'Refreshing...',
	'MCHAT_NO_CUSTOM_PAGE'		=> 'The mChat custom page is not activated at this time!',	
	'MCHAT_NOACCESS'			=> 'You don’t have permission to post in the mChat',
	'MCHAT_NOACCESS_ARCHIVE'	=> 'You don’t have permission to view the archive',	
	'MCHAT_NOJAVASCRIPT'		=> 'Your browser does not support JavaScript or JavaScript is disabled',		
	'MCHAT_NOMESSAGE'			=> 'No messages',
	'MCHAT_NOMESSAGEINPUT'		=> 'You have not entered a message',
	'MCHAT_NOSMILE'				=> 'Smilies not found',
	'MCHAT_OK'					=> 'OK',
	'MCHAT_PERMISSIONS'			=> 'Change users permissions',
	'MCHAT_REPLACE_COPYRIGHT'	=> 'You have removed the copyright from the mChat mod.<br />Please restore the entry for the copyright by editing language/en/mods/mchat_lang.php.<br />Until you do so, both this mod and your forum will not work unless you uninstall mChat.',
	'MCHAT_SMILES'				=> 'Smilies',
	'MCHAT_TITLE'				=> 'Mini-Chat',

	'MCHAT_TOTALMESSAGES'		=> 'Total messages: <strong>%s</strong>',
	'MCHAT_USESOUND'			=> 'Enable sound?',
	
	// whois chatting stuff
	'MCHAT_GUEST_USERS_TOTAL'			=> '%d guests',
	'MCHAT_GUEST_USERS_ZERO_TOTAL'		=> '0 guests',
	'MCHAT_GUEST_USER_TOTAL'			=> '%d guest',	
	'MCHAT_HIDDEN_USERS_TOTAL'			=> '%d hidden',
	'MCHAT_HIDDEN_USERS_TOTAL_AND'		=> '%d hidden and ',
	'MCHAT_HIDDEN_USERS_ZERO_TOTAL'		=> '0 hidden',
	'MCHAT_HIDDEN_USERS_ZERO_TOTAL_AND'	=> '0 hidden and ',
	'MCHAT_HIDDEN_USER_TOTAL'			=> '%d hidden',
	'MCHAT_HIDDEN_USER_TOTAL_AND'		=> '%d hidden and ',
	'MCHAT_ONLINE_USERS_TOTAL'			=> 'In total there are <strong>%d</strong> users chatting :: ',
	'MCHAT_ONLINE_USERS_ZERO_TOTAL'		=> 'In total there are <strong>0</strong> users chatting :: ',
	'MCHAT_ONLINE_USER_TOTAL'			=> 'In total there is <strong>%d</strong> user chatting :: ',	
	'MCHAT_REG_USERS_TOTAL'				=> '%d registered, ',
	'MCHAT_REG_USERS_TOTAL_AND'			=> '%d registered and ',
	'MCHAT_REG_USERS_ZERO_TOTAL'		=> '0 registered, ',
	'MCHAT_REG_USERS_ZERO_TOTAL_AND'	=> '0 registered and ',
	'MCHAT_REG_USER_TOTAL'				=> '%d registered, ',
	'MCHAT_REG_USER_TOTAL_AND'			=> '%d registered and ',	

	
	'WHO_IS_CHATTING'			=> 'Who is chatting',
	'WHO_IS_REFRESH_EXPLAIN'	=> 'Refreshes every <strong>%d</strong> seconds',
	'WHO_IS_REFRESHING'			=> 'Refreshing',
		
	
	// BBCode Font
	'MCHAT_FONTSIZE'	=> 'Font size:',
	'MCHAT_FONTTINY'	=> 'Tiny',
	'MCHAT_FONTSMALL'	=> 'Small',
	'MCHAT_FONTNORMAL'	=> 'Normal',
	'MCHAT_FONTLARGE'	=> 'Large',
	'MCHAT_FONTHUGE'	=> 'Huge',

));
?>