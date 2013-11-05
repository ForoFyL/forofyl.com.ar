<?php
/**
*
* @package phpBB3 Soft Delete Install
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = ((isset($phpbb_root_path)) ? $phpbb_root_path : './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('common');
$user->add_lang('mods/soft_delete');

if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('FOUNDER_ONLY');
}

include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

$auth_admin = new auth_admin();
$db_tool = new phpbb_db_tools($db);

$db_tool->sql_column_add(POSTS_TABLE, 'post_deleted', array('UINT', 0));
$db_tool->sql_column_add(POSTS_TABLE, 'post_deleted_time', array('TIMESTAMP', 0));

$db_tool->sql_column_add(TOPICS_TABLE, 'topic_deleted', array('UINT', 0));
$db_tool->sql_column_add(TOPICS_TABLE, 'topic_deleted_time', array('TIMESTAMP', 0));
$db_tool->sql_column_add(TOPICS_TABLE, 'topic_deleted_reply_count', array('UINT', 0));

$db_tool->sql_column_add(FORUMS_TABLE, 'forum_deleted_topic_count', array('UINT', 0));
$db_tool->sql_column_add(FORUMS_TABLE, 'forum_deleted_reply_count', array('UINT', 0));

$permissions = array(
	'local'      => array(
		'm_harddelete',
	),
	'global'   => array(
		'm_harddelete',
	)
);
$auth_admin->acl_add_option($permissions);

$cache->purge();

trigger_error('SOFT_DELETE_INSTALL_COMPLETE');
?>