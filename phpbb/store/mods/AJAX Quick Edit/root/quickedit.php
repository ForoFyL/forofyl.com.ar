<?php
/** 
*
* @package phpBB3
* @version $Id: quickedit.php 398 2009-09-07 12:12:56Z marc1706 $
* @copyright (c) 2006 StarTrekGuide Group, (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('posting');

$post_id = request_var('post_id', 0);
$submit = isset($_POST['submit']) ? true : false;
$contents = utf8_normalize_nfc(urldecode(request_var('contents', '', true)));


if ($contents == 'cancel')
{
	$sql = 'SELECT *
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . $post_id;	
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	
	//now check to see if the user is allowed to read the post
	if (!$auth->acl_get('f_read', $row['forum_id']) && $row['forum_id'] != 0)
	{
		die($user->lang['USER_CANNOT_READ']);
	}
	
	$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
	$text = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

	$template->assign_vars(array(
		'SEND_TEXT'				=> true,
		'TEXT'      			=> utf8_normalize_nfc($text),
		'EDIT_IMG' 				=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
	));
}
elseif ($submit)
{
	$sql = 'SELECT p.*, f.*, t.*, u.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
	WHERE p.post_id = $post_id AND p.topic_id = t.topic_id AND p.forum_id = f.forum_id AND p.poster_id = u.user_id";	
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
		die($user->lang(($post_data['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED'));	
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
	
	//now check if we need to set the edit time and edit count
	if (!$auth->acl_get('m_edit', $row['forum_id']))
	{
		$edit_time = time();
		$edit_count = $row['post_edit_count'] + 1;
		$edit_user = $user->data['user_id'];
	}
	else 
	{
		if(isset($row['post_edit_time']))
        {
            $edit_time = $row['post_edit_time'];
        }
		else
		{
            $edit_time = 0;
        }
        if(isset($row['post_edit_user']))
        {
            $edit_user = $row['post_edit_user'];
        }
		else
		{
            $edit_user = 0;
        }
        if(isset($row['post_edit_count']))
        {
            $edit_count = $row['post_edit_count'];
        }
		else
		{
            $edit_count = 0;
        } 
	}
	
	// Add moderator edit to the moderator log
	if ($auth->acl_get('m_edit', $row['forum_id']))
	{
		add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_POST_EDITED', $row['topic_title'], (!empty($row['username'])) ? $row['username'] : $user->lang['GUEST']);
	}
	
	$post_text = utf8_normalize_nfc(request_var('contents', '', true));
	$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
	$allow_bbcode = $allow_urls = $allow_smilies = true;
	generate_text_for_storage($post_text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
	
	// let's try something else here in order to get the bbcode_uid and bbcode_bitfield
	$message_parser = new parse_message();
	
	$message_parser->message = $post_text;
	
	if(!isset($uid))
	{
		$uid = $message_parser->bbcode_uid;
	}
	
	if(!isset($bitfield))
	{
		$bitfield = $message_parser->bbcode_bitfield;
	}

	$sql_ary = array(
		'post_text'         => utf8_recode($post_text, 'utf-8'),
		'bbcode_uid'		=> $uid,
		'bbcode_bitfield'   => $bitfield,
		'post_edit_time'	=> $edit_time,
		'post_edit_count'	=> $edit_count,
		'post_edit_user'	=> $edit_user,

	);

	$sql = 'UPDATE ' . POSTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE post_id = $post_id";	
	$db->sql_query($sql);
	
	$text = generate_text_for_display($sql_ary['post_text'], $sql_ary['bbcode_uid'], $sql_ary['bbcode_bitfield'], 7);
	
	$template->assign_vars(array(
		'SEND_TEXT'				=> true,
		'TEXT'					=> $text,
		'EDIT_IMG' 				=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
	));
}
elseif ($post_id)
{
	$sql = 'SELECT p.*, f.*, t.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
	WHERE p.post_id = $post_id AND p.topic_id = t.topic_id";	
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	// HTML, BBCode, Smilies, Images and Flash status
	$bbcode_status	= ($config['allow_bbcode'] && ($auth->acl_get('f_bbcode', $row['forum_id']) || $row['forum_id'] == 0)) ? true : false;
	$smilies_status	= ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $row['forum_id'])) ? true : false;
	$img_status		= ($bbcode_status && $auth->acl_get('f_img', $row['forum_id'])) ? true : false;
	$url_status		= ($config['allow_post_links']) ? true : false;
	$flash_status	= ($bbcode_status && $auth->acl_get('f_flash', $row['forum_id']) && $config['allow_post_flash']) ? true : false;
	$quote_status	= ($auth->acl_get('f_reply', $row['forum_id'])) ? true : false;
	
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
		die($user->lang[(($row['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED')]);
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
	
	if(isset($row['bbcode_uid']))
	{
		decode_message($row['post_text'], $row['bbcode_uid']);
	}
	
	// Build custom bbcodes array
	display_custom_bbcodes();
	
	$template->assign_vars(array(
		'EDIT_FORM'				=> true,
		'POST_ID'				=> $post_id,
		'POST_TEXT'   			=> utf8_normalize_nfc($row['post_text']),
		'EDIT_IMG' 				=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
		'S_LINKS_ALLOWED'			=> $img_status,
		'S_BBCODE_URL'			=> $url_status,
		'S_BBCODE_FLASH'		=> $flash_status,
		'S_BBCODE_QUOTE'		=> $quote_status,
		'S_BBCODE_ALLOWED'		=> $bbcode_status,
		'MAX_FONT_SIZE'			=> (int) $config['max_post_font_size'],
	));
}
else
{
	die('USER_CANNOT_EDIT');
}

$template->set_filenames(array(
	'body' => 'quickedit.html')
);
page_footer();

?>