<?php
/**
*
* @author Coert (Coert Kastelein)
* @package language
* @copyright (c) 2010 KSTLN
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

// umil
$lang = array_merge($lang, array(
	'INSTALL_FACEBOOK'				=> 'Install the Facebook Profile Link',
	'INSTALL_FACEBOOK_CONFIRM'		=> 'Are you ready to install the Facebook Profile Link?',

	'UNINSTALL_FACEBOOK'			=> 'Uninstall the Facebook Profile Link',
	'UNINSTALL_FACEBOOK_CONFIRM'	=> 'Are you ready to uninstall Facebook Profile Link?',
	'UPDATE_FACEBOOK'				=> 'Update Facebook Profile Link',
	'UPDATE_FACEBOOK_CONFIRM'		=> 'Are you ready to update Facebook Profile Link?',
));

// acp_styles
$lang = array_merge($lang, array(
	'IMG_ICON_CONTACT_FACEBOOK'				=> 'Facebook Profile Link',
));

// everywhere else
$lang = array_merge($lang, array(
	'FACEBOOK'				=> 'Facebook',
	'UCP_FACEBOOK'			=> 'Facebook account',
));
