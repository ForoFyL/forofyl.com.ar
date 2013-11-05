<?php
/** 
*
* @package phpBB3
* @version $Id: quickedit.php 373 2007-02-26 22:07:46Z roadydude $
* @copyright (c) 2006 StarTrekGuide Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('posting');

$post_id = request_var('post_id', 0);
$submit = isset($_POST['submit']) ? true : false;
$contents = utf8_normalize_nfc(request_var('contents', '', true));

if ($contents == 'cancel')
{
	$sql = 'SELECT *
	FROM ' . POSTS_TABLE . "
	WHERE post_id = $post_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
	$text = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

	$template->assign_vars(array(
		'SEND_TEXT'	=> true,
		'TEXT'      => ($text),
		'EDIT_IMG' 	=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'=> $user->img('icon_post_delete', 'DELETE_POST'),
	));
}
else if ($submit)
{
	$sql = 'SELECT p.*, f.*, t.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
	WHERE p.post_id = $post_id AND p.topic_id = t.topic_id AND p.forum_id = f.forum_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	//first check to see if we are registered and have rights to edit something here
	if ($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $row['forum_id']))
	{
		$is_authed = true;
	}
	else
	{
		die($user->lang['USER_CANNOT_EDIT']);
	}
	
	//now check to see if this forum is locked and if we aren't a mod that can edit anything
	if (($row['forum_status'] == ITEM_LOCKED || (isset($row['topic_status']) && $row['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $row['forum_id']))
	{
		die(($post_data['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED');
	}
	
	//now check to see if we can edit THIS post
	if (!$auth->acl_get('m_edit', $row['forum_id']))
	{
		if ($user->data['user_id'] != $row['poster_id'])
		{
			die($user->lang['USER_CANNOT_EDIT']);
		}
	
		if (!($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time']))
		{
			die($user->lang['CANNOT_EDIT_TIME']);
		}
	
		if ($row['post_edit_locked'])
		{
			die($user->lang['CANNOT_EDIT_POST_LOCKED']);
		}
	}

	$post_text = utf8_normalize_nfc(request_var('contents', '', true));
	$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
	$allow_bbcode = $allow_urls = $allow_smilies = true;
	generate_text_for_storage($post_text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

	$sql_ary = array(
		'post_text'			=> $post_text,
		'bbcode_uid'		=> $uid,
		'bbcode_bitfield'   => $bitfield,
	);

	$sql = 'UPDATE ' . POSTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE post_id = $post_id";
	$db->sql_query($sql);
	
	$text = generate_text_for_display($sql_ary['post_text'], $sql_ary['bbcode_uid'], $sql_ary['bbcode_bitfield'], 7);
	
	$template->assign_vars(array(
		'SEND_TEXT'	=> true,
		'TEXT'      => ($text),
		'EDIT_IMG' 	=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'=> $user->img('icon_post_delete', 'DELETE_POST'),
	));
}
else if ($post_id)
{
	$sql = 'SELECT p.*, f.*, t.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
	WHERE p.post_id = $post_id AND p.topic_id = t.topic_id AND p.forum_id = f.forum_id";
	$result = $db->sql_query($sql);
	$store_result = $result;
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if(empty($row)) {
		$sql = "SELECT p.*, f.*, t.* FROM ". POSTS_TABLE ." p 
		JOIN ".TOPICS_TABLE." t ON (p.topic_id = t.topic_id) 
		LEFT JOIN ".FORUMS_TABLE." f ON (p.forum_id = f.forum_id) 
		WHERE p.post_id = '$post_id'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$db->sql_freeresult($store_result);
	}
	
	//first check to see if we are registered and have rights to edit something here
	if ($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $row['forum_id']))
	{
		$is_authed = true;
	}
	else
	{
		die($user->lang['USER_CANNOT_EDIT']);
	}
	
	//now check to see if this forum is locked and if we aren't a mod that can edit anything
	if (($row['forum_status'] == ITEM_LOCKED || (isset($row['topic_status']) && $row['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $row['forum_id']))
	{
		die(($post_data['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED');
	}
	
	//now check to see if we can edit THIS post
	if (!$auth->acl_get('m_edit', $row['forum_id']))
	{
		if ($user->data['user_id'] != $row['poster_id'])
		{
			die($user->lang['USER_CANNOT_EDIT']);
		}
	
		if (!($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time']))
		{
			die($user->lang['CANNOT_EDIT_TIME']);
		}
	
		if ($row['post_edit_locked'])
		{
			die($user->lang['CANNOT_EDIT_POST_LOCKED']);
		}
	}

	decode_message($row['post_text'], $row['bbcode_uid']);
	
	$template->assign_vars(array(
		'EDIT_FORM'	=> true,
		'POST_ID'	=> $post_id,
		'POST_TEXT'   => ($row['post_text']),
		'EDIT_IMG' 	=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'=> $user->img('icon_post_delete', 'DELETE_POST'),
	));
}
else
{
	trigger_error('USER_CANNOT_EDIT');
}

$template->set_filenames(array(
	'body' => 'quickedit.html')
);
page_footer();

?>