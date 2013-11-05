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
	'CLICK_RETURN_POST'				=> '%sClick here to return to the post%s',
	'CLICK_RETURN_TOPIC'			=> '%sClick here to return to the topic%s',
	'CLICK_RETURN_FORUM'			=> '%sClick here to return to the forum%s',

	'FOUNDER_ONLY'					=> 'You must be a board founder to access this page.',

	'HARD_DELETE'					=> 'Hard Delete',
	'HARD_DELETE_MESSAGE'			=> 'Delete Message',
	'HARD_DELETE_MESSAGE_CONFIRM'	=> 'Are you sure you want to permanently delete this post?  This can not be un-done.',
	'HARD_DELETE_TOPIC'				=> 'Hard Delete Topic',
	'HARD_DELETE_TOPIC_CONFIRM'		=> 'Are you sure you want to permanently delete this topic?  This can not be un-done.',
	'HARD_DELETE_TOPICS'			=> 'Hard Delete Topics',
	'HARD_DELETE_TOPICS_CONFIRM'	=> 'Are you sure you want to permanently delete these topics?  This can not be un-done.',

	'LOG_HARD_DELETE_POST'			=> '<strong>Deleted post</strong><br />» %s',
	'LOG_HARD_DELETE_TOPIC'			=> '<strong>Deleted topic</strong><br />» %s',
	'LOG_SOFT_DELETE_POST'			=> '<strong>Soft deleted post</strong><br />» %s',
	'LOG_SOFT_DELETE_TOPIC'			=> '<strong>Soft deleted topic</strong><br />» %s',
	'LOG_UNDELETE_POST'				=> '<strong>Undeleted post</strong><br />» %s',
	'LOG_UNDELETE_TOPIC'			=> '<strong>Undeleted topic</strong><br />» %s',

	'POST_HARD_DELETE_SUCCESS'		=> 'The post has been hard deleted successfully.',
	'POST_SOFT_DELETE_SUCCESS'		=> 'The post has been soft deleted successfully.',
	'POST_UNDELETE_SUCCESS'			=> 'The post has been undeleted successfully.',
	'POSTS_MCP_DELETE_SUCCESS'		=> 'The posts have been deleted successfully.',

	'SOFT_DELETE_INSTALL_COMPLETE'	=> 'The Database changes have been completed successfully!<br />Please delete this file.',
	'SOFT_DELETE_MESSAGE'			=> 'Soft Delete Message',
	'SOFT_DELETE_MESSAGE_CONFIRM'	=> 'Are you sure you want to soft delete this message?',

	'TOPIC_HARD_DELETE_SUCCESS'		=> 'The topic has been hard deleted successfully.',
	'TOPIC_SOFT_DELETED'			=> 'This topic has been deleted.',
	'TOPIC_SOFT_DELETE_SUCCESS'		=> 'The topic has been soft deleted successfully.',
	'TOPIC_UNDELETE_SUCCESS'		=> 'The topic has been undeleted successfully.',
	'TOPICS_MCP_DELETE_SUCCESS'		=> 'The topics have been deleted successfully.',

	'UNDELETE_POST'					=> 'Undelete Post',
	'UNDELETE_POST_CONFIRM'			=> 'Are you sure you want to undelete this post?',
));

?>