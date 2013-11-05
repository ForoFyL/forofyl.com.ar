<?php
/**
* acp_permissions_mchat (phpBB Permission Set) [English]
*
* @package language
* @version $Id: permissions_phpbbmchat.php 8911 2009-08-20 13:58:33Z rmcgirr83 $
* @copyright (c) 2009 phpbb3bbcodes.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

// Adding new category
$lang['permission_cat']['mchat'] = 'mChat';

// Adding the permissions
$lang = array_merge($lang, array(
	// User perms
	'acl_u_mchat_use'			=> array('lang' => 'Can use mChat', 'cat' => 'mchat'),
	'acl_u_mchat_view'			=> array('lang' => 'Can view mChat', 'cat' => 'mchat'),
	'acl_u_mchat_edit'			=> array('lang' => 'Can edit mChat messages', 'cat' => 'mchat'),
	'acl_u_mchat_delete'		=> array('lang' => 'Can delete mChat messages', 'cat' => 'mchat'),
	'acl_u_mchat_ip'			=> array('lang' => 'Can use view mChat IP addresses', 'cat' => 'mchat'),
	'acl_u_mchat_flood_ignore'	=> array('lang' => 'Can ignore mChat flood', 'cat' => 'mchat'),
	'acl_u_mchat_archive'		=> array('lang' => 'Can view the Archive', 'cat' => 'mchat'),
	'acl_u_mchat_bbcode'		=> array('lang' => 'Can use bbcode in mChat', 'cat' => 'mchat'),
	'acl_u_mchat_smilies'		=> array('lang' => 'Can use smilies in mChat', 'cat' => 'mchat'),
	'acl_u_mchat_urls'			=> array('lang' => 'Can post urls in mChat', 'cat' => 'mchat'),

	// Admin perms
	'acl_a_mchat'				=> array('lang' => 'Can manage mChat settings', 'cat' => 'permissions'), // Using a phpBB category here
));

?>