<?php
/** 
*
* contact [English]
*
* @package	language
* @version	1.0.9 2009-12-25
* @copyright(c) 2009 RMcGirr83
* @copyright (c) 2007 eviL3
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

	// teh form
	
	'CONTACT_BOT_ERROR'						=> 'You can’t use the contact form at the moment because there is an error in the configuration.  An email has been sent to the board founder.',
	'CONTACT_BOT_NONE'						=> 'The user %1$s tried to use the Contact Admin Modification at %2$s to send a %3$s, but there are no Administrators that allow %3$ss by users.' . "\n\n" . 'Please enter the Contact Bot Configuration in the Admin Panel for the forum %4$s and choose the “Board Founder” option' . "\n\n" . 'The modification has been disabled',
	'CONTACT_BOT_SUBJECT'					=> 'Contact Board Administration Modification Error',
	'CONTACT_BOT_USER_MESSAGE'				=> 'The user %1$s tried to use the Contact Admin modification at %2$s, but the user selected in the configuration is incorrect.' . "\n\n" . 'Please visit the forum %3$s and choose a different user in the ACP for the Contact Board Administration.' . "\n\n" . 'The modification has been disabled',
	'CONTACT_BOT_FORUM_MESSAGE'				=> 'The user %1$s tried to use the Contact Admin modification at %2$s, but the forum selected in the configuration is incorrect.' . "\n\n" . 'Please visit the forum %3$s and choose a different forum in the ACP for the Contact Board Administration.' . "\n\n" . 'The modification has been disabled',
	'CONTACT_CONFIRM'						=> 'Confirm',
	'CONTACT_INSTALLED'						=> 'The “Contact Board Administration” modification has been installed successfully.',

	'CONTACT_DISABLED'			=> 'You can’t use the contact form at the moment because it is disabled.',
	'CONTACT_MAIL_DISABLED'		=> 'There is an error with the configuration of the Contact Board Administration Mod.<br />The mod is set to send an email but the board configuration isn’t setup to send emails.  Please notify the board administrator or webmaster: <a href="mailto:%1$s">%1$s</a>', 
	'CONTACT_MSG_SENT'			=> 'Your message has been sent successfully',
	'CONTACT_MSG_BODY_EXPLAIN'	=> '<br /><span>Please use the contact form <strong><em>only</em></strong> if there is no other way to contact us.<br /><br /><span style="text-align:center;"><strong>Your ΙΡ address is being recorded and any abuse attempt will be punished.</strong></span></span>',
	'CONTACT_NO_NAME'			=> 'You didn’t enter a name.',
	'CONTACT_NO_EMAIL'			=> 'You didn’t enter an email address.',
	'CONTACT_NO_MSG'			=> 'You didn’t enter a message.',
	'CONTACT_NO_SUBJ'			=> 'You didn’t enter a subject.',
	'CONTACT_NO_REASON'			=> 'You didn’t enter a valid reason.',
	'CONTACT_OUTDATED'			=> 'The database for the contact page has not been updated yet. Please wait for an administrator to update it.',
	'CONTACT_REASON'			=> 'Reason',
	'CONTACT_TEMPLATE'			=> '<strong>Name:</strong> %1$s' . "\n" . '<strong>Email Address:</strong> %2$s' . "\n" . '<strong>IP:</strong> %3$s' . "\n" . '<strong>Date:</strong> %4$s' . "\n" . '<strong>Reason:</strong> %5$s' . "\n" . '<strong>Subject:</strong> %6$s' . "\n\n" . '<strong>Has entered the following message into the contact form:</strong>' . "\n" . '%7$s',
	'CONTACT_TEMPLATE_NO_REASON'	=> '<strong>Name:</strong> %1$s' . "\n" . '<strong>Email Address:</strong> %2$s' . "\n" . '<strong>IP:</strong> %3$s' . "\n" . '<strong>Date:</strong> %4$s' . "\n" . '<strong>Subject:</strong> %5$s' . "\n\n" . '<strong>Has entered the following message into the contact form:</strong>' . "\n" . '%6$s',
	'CONTACT_TITLE'				=> 'Contact Board Administration',
	'CONTACT_TOO_MANY'			=> 'You have exceeded the maximum number of contact confirmation attempts for this session. Please try again later.',
	'CONTACT_UNINSTALLED'		=> 'The “contact board administration” modification has been uninstalled successfully.',
	'CONTACT_UPDATED'			=> 'The “contact board administration” modification has been updated to version %s successfully.',
	
	'CONTACT_YOUR_NAME'				=> 'Your name',
	'CONTACT_YOUR_NAME_EXPLAIN'		=> 'Please enter your name, so the message has an identity.',
	'CONTACT_YOUR_EMAIL'			=> 'Your email address',
	'CONTACT_YOUR_EMAIL_EXPLAIN'	=> 'Please enter a valid email address, so we can contact you.',
	'CONTACT_YOUR_EMAIL_CONFIRM'	=> 'Reenter your email address',
	'CONTACT_YOUR_EMAIL_CONFIRM_EXPLAIN'	=> 'Please re-enter your email address.',	

	'RETURN_CONTACT'				=> '%sReturn to the contact page%s',
	'URL_UNAUTHED'		=> 'You can not post urls, please remove or rename:<br /><em>%1$s</em>',
));

?>