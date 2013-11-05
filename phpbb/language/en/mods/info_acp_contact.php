<?php

/**
*
* @package - Contact Board admin
* @version $Id: info_acp_contact.php 1.0.9 2009-12-25 RMcGirr83 $
* @copyright (c) 2009 RMcGirr83 http://rmcgirr83.org
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

	// ACP entries
	'LOG_CONTACT_CONFIG_UPDATE'		=> '<strong>Updated Contact Board Administration config settings</strong>',
	
	// General config options
	// Only needed to remove module from previous version installs
	'ACP_CONTACT_ADMIN_SETTINGS'	=> 'Contact Board Administration',	
	
	'ACP_CONTACT_SETTINGS_EXPLAIN'	=> 'This is the configuration page for the “Contact Board Administration” MOD by RMcGirr83 previous contributor eviL&lt;3.',

	'ACP_CONTACT_CONFIG'			=> 'Configuration',
	'ACP_CAT_CONTACT'				=> 'Contact Board Administration',	
	'CONTACT_CONFIG_SAVED'			=> 'Contact Board Administration configuration has been updated',
	'CONTACT_VERSION'				=> 'Version:',
	'CONTACT_ENABLE'				=> 'Enable Contact Board Administration MOD',
	'CONTACT_ENABLE_EXPLAIN'		=> 'Enable or disable the mod globally.',

	'CONTACT_ACP_CONFIRM'				=> 'Enable visual confirmation',
	'CONTACT_ACP_CONFIRM_EXPLAIN'		=> 'If you enable this option, users will have to enter a visual confirmation to send the message.<br />This is to prevent automized messages. Note that this option is for the contact page only.  It does not affect other visual confirmation settings.',
	'CONTACT_ATTACHMENTS'				=> 'Attachments allowed',
	'CONTACT_ATTACHMENTS_EXPLAIN'		=> 'If set attachments will be allowed in posting to the forum and private messages.<br />The extensions allowed are the same as the board configuration.<br /><span style="color:red;">Does not apply for contact method via “EMail”.</span>',
	'CONTACT_BBCODES'					=> 'BBcodes allowed',
	'CONTACT_BBCODES_EXPLAIN'			=> 'If set bbcodes will be allowed to be used.<br /><span style="color:red;">Does not apply for contact method via “EMail”.</span>',
	'CONTACT_CONFIRM_GUESTS'			=> 'Visual confirmation for guests only',
	'CONTACT_CONFIRM_GUESTS_EXPLAIN'	=> 'If this option is enabled, the visual confirmation is only displayed to guests (if it\'s enabled).',
	'CONTACT_ENABLE'					=> 'Enable contact page',
	'CONTACT_ENABLE_EXPLAIN'			=> 'If disabled, the contact page will display a message when you visit it and the link will not display in the header.',	
	'CONTACT_FOUNDER'					=> 'Contact via just the Board Founder',
	'CONTACT_FOUNDER_EXPLAIN'			=> 'If set only the Founder of the Forum will get Email or PM notifications.',
	'CONTACT_GENERAL_SETTINGS'			=> 'General contact page settings',
	'CONTACT_MAX_ATTEMPTS'				=> 'Maximum confirmation attempts',
	'CONTACT_MAX_ATTEMPTS_EXPLAIN'		=> 'How many times may a user attempt to enter the correct confirmation image?<br />Enter 0 for unlimited attempts.',
	'CONTACT_METHOD'					=> 'Contact method',
	'CONTACT_METHOD_EXPLAIN'			=> 'How do you want users to be able to make contact.<br /><span style="color:red;">If set as “EMail”, then attachments, bbcodes and smilies do not apply.</span>',
	'CONTACT_REASONS'					=> 'Contact reasons',
	'CONTACT_REASONS_EXPLAIN'			=> 'Enter reasons for contacting, separated by new lines.<br />If you don\'t want to use this feature, leave this field empty.',
	'CONTACT_SMILIES'					=> 'Are smilies allowed',
	'CONTACT_SMILIES_EXPLAIN'			=> 'If set then smilies are allowed to be used.<br /><span style="color:red;">Does not apply for contact method via “EMail”.</span>',
	'CONTACT_URLS'						=> 'URLs allowed',
	'CONTACT_URLS_EXPLAIN'				=> 'If set and BBcodes are allowed URLs will be allowed to be used.<br /><span style="color:red;">Does not apply for contact method via “EMail”.</span>',
	// Bot config options
	'CONTACT_BOT_FORUM'				=> 'Contact bot forum',
	'CONTACT_BOT_FORUM_EXPLAIN'		=> 'Select the forum, where the contact bot should post to, if the contact method is set to “Forum post”.',
	'CONTACT_BOT_POSTER'			=> 'Bot as Poster',
	'CONTACT_BOT_POSTER_EXPLAIN'	=> 'If set PM\'s and posts will seem to come from the contact bot user chosen above based on the settings here.  If “Neither” is selected then the bot is not used as the poster.  Posts and PM\'s will be posted based on the information entered in the contact form.',
	'CONTACT_BOT_USER'				=> 'Contact bot user',
	'CONTACT_BOT_USER_EXPLAIN'		=> 'Select the user that messages will be posted under if the contact method is set to “Private Message” or “Forum Post”.',
	'CONTACT_USERNAME_CHK'			=> 'Check Username',
	'CONTACT_USERNAME_CHK_EXPLAIN'	=> 'If set yes, the users name that is entered will be checked against those in the database.  If a similar name is found the user will be presented with an error and asked to input a different user name.',
	'CONTACT_EMAIL_CHK'				=> 'Check Email',
	'CONTACT_EMAIL_CHK_EXPLAIN'		=> 'If set yes, the users email will be checked against those in the database.  If a similar email is found the user will be presented with an error and asked to input a different email address.',

	// Contact methods
	'CONTACT_METHOD_EMAIL'			=> 'Email',
	'CONTACT_METHOD_PM'				=> 'Private message',
	'CONTACT_METHOD_POST'			=> 'Forum post',
	
	// Contact posters...user bot
	'CONTACT_POST_NEITHER'			=> 'Neither',
	'CONTACT_POST_GUEST'			=> 'Guests only',
	'CONTACT_POST_ALL'				=> 'Everyone',		
	
	// UMIL stuff
	'ACP_CONTACT_TITLE'				=> 'Contact Board Admin',
	'ACP_CONTACT_TITLE_EXPLAIN'		=> 'An area for guests and users to contact the Board Administration',
	'CONTACT_UPDATED'				=> 'Database entries updated',
	'CONTACT_INSTALLED'				=> 'Database entries installed',
	
	// Log entries
	'LOG_CONFIG_CONTACT_ADMIN'		=> '<strong>Altered Contact Board Administration mod page settings</strong>',
	'LOG_CONTACT_BOT_INVALID'		=> '<strong>The Contact Board Administration mod bot has an invalid user id selected:</strong><br />User ID %1$s',
	'LOG_CONTACT_FORUM_INVALID'		=> '<strong>The Contact Board Administration mod forum has an invalid forum selected:</strong><br />Forum ID %1$s',
	'LOG_CONTACT_NONE'				=> '<strong>No Administrators are allowing users to contact them via %1$s in the Contact Board Administration mod</strong>',	
	
));

?>