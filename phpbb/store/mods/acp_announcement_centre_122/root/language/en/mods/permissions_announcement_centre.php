<?php
/**
* permissions_announcement_centre [English]
*
* @package language
* @version $Id: permissions_announcement_centre.php 127 2008-10-15 21:43:34Z lefty74 $
* @copyright (c) 2008 lefty74
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

// Adding new category
$lang['permission_cat']['announcement_centre']   = 'ACP Announcement Centre';

// Adding the permissions
$lang = array_merge($lang, array(
   // Moderator perms
   'acl_m_announcement_centre'      => array('lang' => 'Moderator can change Site Announcements', 'cat' => 'announcement_centre'),

	// Admin perms
   'acl_a_announcement_centre'      => array('lang' => 'Admin can change Site Announcements / Config', 'cat' => 'announcement_centre'),
));

?>