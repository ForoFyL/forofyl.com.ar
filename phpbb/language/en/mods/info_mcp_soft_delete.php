<?php
/**
* @package phpBB3 Soft Delete
* @copyright (c) 2007 EXreaction,
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'LOG_HARD_DELETE_POST'			=> '<strong>Deleted post</strong><br />> %s',
	'LOG_HARD_DELETE_TOPIC'			=> '<strong>Deleted topic</strong><br />> %s',
	'LOG_SOFT_DELETE_POST'			=> '<strong>Soft deleted post</strong><br />> %s',
	'LOG_SOFT_DELETE_TOPIC'			=> '<strong>Soft deleted topic</strong><br />> %s',
	'LOG_UNDELETE_POST'				=> '<strong>Undeleted post</strong><br />> %s',
	'LOG_UNDELETE_TOPIC'			=> '<strong>Undeleted topic</strong><br />> %s',
));

?>