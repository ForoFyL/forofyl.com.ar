<?php
/** 
*
* acp_announcements_centre [English]
*
* @package language
* @version $Id: announcement_centre.php 191 2009-03-17 17:56:06Z lefty74 $ 
* @copyright (c) 2007 lefty74
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
* DO NOT CHANGE
*/
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

// Announcement  settings
$lang = array_merge($lang, array(
	'ACP_ANNOUNCEMENT_TITLE'					=> 'ACP Announcement Centre',
	'ACP_ANNOUNCEMENT_TITLE_EXPLAIN'			=> 'The form will allow you to write your Site Announcements. You can select who should see these announcements. You can have alternative announcements for guests.',
	'ANNOUNCEMENTS_CONFIG'					=> 'Announcements Configuration',
	
	'ANNOUNCEMENT_ENABLE'					=> 'Show Site Announcements',
	'ANNOUNCEMENT_ALIGN'					=> 'Site Announcement alignment',
	'ANNOUNCEMENT_ALIGN_EXPLAIN'			=> 'Indicate how you would like the Site Announcement aligned.',
	'ANNOUNCEMENT_GUESTS_ALIGN'				=> 'Guest Announcement alignment',
	'ANNOUNCEMENT_GUESTS_ALIGN_EXPLAIN'		=> 'Indicate how you would like the Guest Announcement aligned.',
	'ANNOUNCEMENT_SHOW'						=> 'Show Site Announcement to',
	'ANNOUNCEMENT_SHOW_INDEX'				=> 'Show Announcements only on Index page',
	'ANNOUNCEMENT_SHOW_BIRTHDAYS'			=> 'Show Birthdays as announcements',
	'ANNOUNCEMENT_SHOW_BIRTHDAYS_EXPLAIN'	=> 'Shows the current birthdays (if birthdays are enabled) as announcements (takes priority to Site Announcement Text) unless set to shown both',
	'ANNOUNCEMENT_SHOW_GROUP'				=> 'Choose Group(s) that should see the announcement',
	'ANNOUNCEMENT_SHOW_GROUP_EXPLAIN'		=> 'Only applicable if announcement is shown to Groups',
	'ANNOUNCEMENT_BIRTHDAY_AVATAR'			=> 'Show avatars in birthdays announcements',
	'ANNOUNCEMENT_BIRTHDAY_AVATAR_EXPLAIN'	=> 'Also shows the avatar of the birthday persons',
	'ANNOUNCEMENT_DRAFT_PREVIEW'			=> 'Draft Announcement Preview',
	'ANNOUNCEMENT_TITLE'					=> 'Site Announcement Title',
	'ANNOUNCEMENT_TITLE_EXPLAIN'			=> 'Customise the Announcement Block Title here <br/>Leave blank to use default language variable',
	'ANNOUNCEMENT_DRAFT'					=> 'Announcement Draft',
	'ANNOUNCEMENT_DRAFT_EXPLAIN'			=> 'Draft here your Announcement text',
	'ANNOUNCEMENT_TEXT'						=> 'Announcement Text',
	'ANNOUNCEMENT_TEXT_EXPLAIN'				=> 'Write here your Announcement text:<br/>Enter Forum ID, Topic ID or Post ID to use the first or latest post as announcement text. <br/>Announcement text is populated in the following order:<br/>1. Forum ID, if none entered then<br/>2. Topic ID, if none entered then<br/>3. Post ID, if none entered then<br/>4. Custom announcement text',
	'ANNOUNCEMENT_TEXT_MCP_EXPLAIN'			=> 'You can change the announcement text here.',
	'ANNOUNCEMENT_TEXT_MCP_EXPLAIN_NOTE'	=> ' Note: This is only possible if the announcement text is not post text.',
	'ANNOUNCEMENT_TEXT_MCP_NOEDIT'			=> 'CHANGING OF ANNOUNCEMENTS CURRENTLY NOT POSSIBLE!',
	'ANNOUNCEMENT_ENABLE_GUESTS'			=> 'Show different Announcement to guests',
	'ANNOUNCEMENT_ENABLE_GUESTS_EXPLAIN'	=> 'Shows different Announcement for guest users except when Show Site Announcement to is set to Guests or Everyone',
	'ANNOUNCEMENT_TITLE_GUESTS'				=> 'Guest Announcement Title',
	'ANNOUNCEMENT_TITLE_GUESTS_EXPLAIN'		=> 'Customise the Guest Announcement Block Title here <br/>Leave blank to use default language variable',
	'ANNOUNCEMENT_TEXT_GUESTS'				=> 'Guest Announcement Text',
	'ANNOUNCEMENT_TEXT_GUESTS_EXPLAIN'		=> 'Write here your Guest Announcement text',
	'ACP_ANNOUNCEMENTS_CENTRE'				=> 'Announcement Centre',
	'ACP_ANNOUNCEMENTS_CENTRE_CONFIG'		=> 'Announcement Centre Configuration',

	'MCP_ANNOUNCEMENTS_CENTRE'				=> 'MCP Announcement Centre',
	'MCP_ANNOUNCE_FRONT'					=> 'MCP Front page',

	'ANNOUNCEMENT_UPDATED'					=> 'Announcement updated!',
	'ANNOUNCEMENT_GOTOPOST'					=> '[Continue]',
	'ANNOUNCEMENT_GOPOST'					=> 'Show link to post',
	'ANNOUNCEMENT_GOPOST_EXPLAIN'			=> 'If a post is shown as announcement, a link to the post will be shown',

	'COPY_TO_ANNOUNCEMENT_SHORT'			=> 'Copy to Site Text',
	'COPY_TO_GUEST_ANNOUNCEMENT_SHORT'		=> 'Copy to Guest Text',
	'COPY_TO_ANNOUNCEMENT'					=> 'Copy to Site Announcement Text',
	'COPY_TO_GUEST_ANNOUNCEMENT'			=> 'Copy to Guest Announcement Text',

	'ANNOUNCEMENT_SHOW_BIRTHDAYS_AND_ANNOUNCE' 			=> 'Show Birthdays and Announcements at same time',
	'ANNOUNCEMENT_SHOW_BIRTHDAYS_AND_ANNOUNCE_EXPLAIN' 	=> 'This will show birthdays and the announcement text at the same time. Avatars will not be shown for birthdays when this is set to yes',

	'ANNOUNCEMENT_AVA_MAX_SIZE'				=> 'Max size of avatar:',
	'ANNOUNCEMENT_AVA_MAX_SIZE_EXPLAIN'		=> 'Enter the maximum size the avatars should be in the birthday announcements:',
	
	'ANNOUNCEMENT_SHOW_BIRTHDAYS_ALWAYS' 				=> 'Show Birthdays as announcement even if show site announcements is set to no',
	'ANNOUNCEMENT_SHOW_BIRTHDAYS_ALWAYS_EXPLAIN' 		=> 'This will display the birthdays as announcements, even if the regular announcements are turned off. This option is not active when regular announcements are turned on.',

	'ANNOUNCEMENT_FORUM_ID'			=> 'Forum ID',
	'ANNOUNCEMENT_TOPIC_ID'			=> 'Topic ID',
	'ANNOUNCEMENT_POST_ID'			=> 'Post ID',
	'ANNOUNCEMENT_LATEST_POST'		=> 'Latest Post',
	'ANNOUNCEMENT_FIRST_POST'		=> 'First Post',
	'ANNOUNCEMENT_EVERYONE'			=> 'Everyone',
	'ANNOUNCEMENT_GROUPS'			=> 'Groups',
	'ANNOUNCEMENT_GUESTS'			=> 'Guests',
	'ANNOUNCEMENT_LEFT_ALIGNED'		=> 'Left',
	'ANNOUNCEMENT_CENTER_ALIGNED'	=> 'Center',
	'ANNOUNCEMENT_RIGHT_ALIGNED'	=> 'Right',
	
	//Installation vars
	// Installation file stuff, not needed anymore after installation is complete
	'AC_TABLE_CREATED'		=> 	'Announcement Centre Table and Config fields created.',
	'AC_MODULE_ADDED'		=> 	'Announcement Centre Module has been added.',
	'AC_CONFIGS_CREATED'	=> 	'Announcement Centre config fields have been created.',
	'AC_VERSION_UPDATED'	=> 	'Announcement Centre version updated.',
	'AC_INSTALL_COMPLETE'	=> 	'<strong>Announcement Centre installation complete. Please delete this folder (/install)!!</strong>',
	'AC_INSTALL_RETURN'		=> 	'<br /><br /><br />Click %shere%s to return to the board index.',
	'AC_INSTALL_REDIRECT'	=> 	'Please wait while you are being redirected to complete the installation.',
	'AC_UNINSTALL_REDIRECT'	=> 	'Please wait while you are being redirected to complete the deletion.',

	'AC_PREV_TABLE_DELETE'	=>	'Announcement Centre table deleted   <br />',
	'AC_MODULE_READDED'		=> 	'Announcement Centre Module has been re-added.',

	'AC_TABLE_CONFIG_DELETE'	=> 	'Announcement Centre Table and Config fields deleted   <br />',
	'AC_MODULE_DELETED'			=> 	'Announcement Centre Module has been deleted   <br />',
	'AC_DELETE_COMPLETE'		=> 	'<strong>Announcement Centre deletion complete. Please delete this folder (/install)!!	</strong>',
	'AC_BACKUP_WARN'			=> 	'Make sure you have backed up your database before proceeding!!!',
	'AC_INSTALL_DESC'			=> 	'This installation file will create the Database table/fields and add the appropriate module. <br />To proceed please click on the appropriate action below:',
	'AC_UPGRADE_DESC'			=> 	'This installation file will upgrade/delete the Database table/fields and add/remove the appropriate module. <br />To proceed please click on the appropriate action below:',
	
	'AC_NEW_INSTALL'		=> 	'New Installation',
	'AC_UPGRADE'			=> 	'Upgrade to %s',
	'AC_UP_TO_DATE'			=> 	'Version %s is currently installed on your system which is the latest up-to-date version',
	'AC_UNINSTALL'			=> 	'Uninstall',
	'AC_PERM_CREATED'		=> 	'Announcement Centre permissions created.',

																			
	'AC_DESCRIPTION' 		=>	'Adds an Announcement Box to the Index Page.',
));

?>