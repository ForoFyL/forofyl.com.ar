<?php
/**
*
* @package phpBB3
* @version $Id: functions_announcements.php 240 2009-11-01 20:38:17Z lefty74 $
* @copyright (c) 2008,2009 lefty74
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
* Generate list of groups (option fields with select) 
* note: This is a modified function from functions_admin.php
* @param int $group_ids The groupids to mark as selected
* @param array $exclude_ids The group ids to exclude from the list, false (default) if you whish to exclude no id
* @param int $manage_founder If set to false (default) all groups are returned, if 0 only those groups returned not being managed by founders only, if 1 only those groups returned managed by founders only.
*
* @return string The list of options.
*/
function group_select_options_selected($group_ids, $exclude_ids = false, $manage_founder = false)
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $config;
	

	$exclude_sql = ($exclude_ids !== false && sizeof($exclude_ids)) ? 'WHERE ' . $db->sql_in_set('group_id', array_map('intval', $exclude_ids), true) : '';
	$sql_and = (!$config['coppa_enable']) ? (($exclude_sql) ? ' AND ' : ' WHERE ') . "group_name <> 'REGISTERED_COPPA'" : '';
	$sql_founder = ($manage_founder !== false) ? (($exclude_sql || $sql_and) ? ' AND ' : ' WHERE ') . 'group_founder_manage = ' . (int) $manage_founder : '';

	$sql = 'SELECT group_id, group_name, group_type
		FROM ' . GROUPS_TABLE . "
		$exclude_sql
		$sql_and
		$sql_founder
		ORDER BY group_type DESC, group_name ASC";
	$result = $db->sql_query($sql);
	
	$s_group_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
			
		$selected = (in_array($row['group_id'], $group_ids, true)) ? ' selected="selected"' : '';
		$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

	return $s_group_options;
}

/**
* get all the announcement data
*
* @param string $birthday_list, true or false
*/
function get_announcement_data()
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $config;

	$user->add_lang('mods/announcement_centre');

	$announcement_birthday_list = $announcement_text = $announcement_birthday_img = '';
	if ($config['load_birthdays'] && $config['allow_birthdays'])
	{
		// Generate birthday list if required ...
		$now = getdate(time() + $user->timezone + $user->dst - date('Z'));
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . USERS_TABLE . ' u
			LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
			WHERE (b.ban_id IS NULL
				OR b.ban_exclude = 1)
				AND u.user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%'
				AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			//obtain the avatar and username for the birthday announcements
			$max_bdavatar_size = $bdavatar_width = $bdavatar_height = '';
			if ( !empty($row['user_avatar']) )
			{
				$max_bdavatar_size = $config['announcement_ava_max_size'];
			
				if ( $row['user_avatar_width'] >= $row['user_avatar_height'] )
				{
					$bdavatar_width = ( $row['user_avatar_width'] > $max_bdavatar_size ) ? $max_bdavatar_size : $row['user_avatar_width'] ;
					$bdavatar_height = ( $bdavatar_width == $max_bdavatar_size ) ? round($max_bdavatar_size / $row['user_avatar_width'] * $row['user_avatar_height']) : $row['user_avatar_height'] ;
				}
				else 
				{
					$bdavatar_height = ( $row['user_avatar_height'] > $max_bdavatar_size ) ? $max_bdavatar_size : $row['user_avatar_height'] ;
					$bdavatar_width = ( $bdavatar_height == $max_bdavatar_size ) ? round($max_bdavatar_size / $row['user_avatar_height'] * $row['user_avatar_width']) : $row['user_avatar_width'] ;
				}
			}
			
			if ( !function_exists('get_user_avatar') ) // only  checking for one of the functions as the other is in the same file
			{
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
			}

			$announcement_birthday_list .= (($announcement_birthday_list != '') ? ', ' : '') . get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

			$template->assign_block_vars('bdannounce', array(
			'ANNOUNCEMENT_AVATAR'	=> ($row['user_avatar']) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $bdavatar_width, $bdavatar_height, $row['username']) : '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/theme/images/no_avatar.gif" height="' . $config['announcement_ava_max_size'] . '" width="' . $config['announcement_ava_max_size'] . '" title="" alt=""  />',
			'ANNOUNCEMENT_USERNAME'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])));

		}
		$db->sql_freeresult($result);
	}

	// Generate the announcement data
	$sql = 'SELECT * 
		FROM ' . ANNOUNCEMENTS_CENTRE_TABLE;
	$result = $db->sql_query($sql);
	$announcement = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$selected_groups = array();
	$selected_groups = explode(",", $config['announcement_show_group']);

	$sql = 'SELECT *
		FROM ' . USER_GROUP_TABLE . '
		WHERE ' . $db->sql_in_set('group_id', $selected_groups) . '
			AND user_id = ' . $user->data['user_id'];
	$db->sql_query($sql);
	$result = $db->sql_query_limit($sql,1,0);
	$is_in_group = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	//Announcement Centre by lefty74
	if ( ($user->data['user_id']) == ANONYMOUS && $config['announcement_show'] == GUESTS_ONLY ) // Guests only
	{
		$announcement_show = true;
		$announcement_show_everyone_guests = true;
	}
	else if ( $user->data['user_id'] != ANONYMOUS  && ($is_in_group && $config['announcement_show'] == GROUPS_ONLY) ) // Groups only
	{
		$announcement_show = true;
		$announcement_show_everyone_guests = false;
	}
	else if ( $config['announcement_show'] == EVERYONE ) // Everyone
	{
		$announcement_show = true;
		$announcement_show_everyone_guests = true;
	}
	else 
	{
		$announcement_show = false;
		$announcement_show_everyone_guests = false;
	}

	$announcement_birthday_img = '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/birthday.png" title="' . $user->lang['CONGRATULATIONS'] . '" alt="' . $user->lang['CONGRATULATIONS'] . '" />';	
	
	$where_sql = $order = '';
	
	if ( $announcement['announcement_forum_id'] || $announcement['announcement_topic_id'] || $announcement['announcement_post_id'])
	{
		if ($announcement['announcement_forum_id'])
		{
			$where_sql = 'WHERE forum_id = ' . (int) $announcement['announcement_forum_id'];		
		}
		else if ($announcement['announcement_topic_id'])
		{
			$where_sql = 'WHERE topic_id = ' . (int) $announcement['announcement_topic_id'];		
		}
		else if ($announcement['announcement_post_id'])
		{
			$where_sql = 'WHERE post_id = ' . (int) $announcement['announcement_post_id'];					
		}
		
		
		$announcement_text = announcement_post($where_sql, (string) $announcement['announcement_first_last_post'], (int) $announcement['announcement_gopost']);
	}
	else 
	{
		$announcement_text = generate_text_for_display($announcement['announcement_text'],$announcement['announcement_text_bbcode_uid'], $announcement['announcement_text_bbcode_bitfield'], $announcement['announcement_text_bbcode_options']);
	}
	

// Assign index specific vars
	$template->assign_vars(array(
		'ANNOUNCEMENT_TEXT' 			=> $announcement_text,
		'ANNOUNCEMENT_BIRTHDAYS' 		=> $announcement_birthday_list,
		'ANNOUNCEMENT_BIRTHDAY_IMG'		=> $announcement_birthday_img,
		'ANNOUNCEMENT_TITLE_GUESTS'		=> $announcement['announcement_title_guests'],
		'ANNOUNCEMENT_TEXT_GUESTS'		=> generate_text_for_display($announcement['announcement_text_guests'],$announcement['announcement_text_guests_bbcode_uid'], $announcement['announcement_text_guests_bbcode_bitfield'], $announcement['announcement_text_guests_bbcode_options']),
		'ANNOUNCEMENT_TITLE' 			=> $announcement['announcement_title'],
		'ANNOUNCEMENT_TITLE_GUESTS' 	=> $announcement['announcement_title_guests'],
		'ANNOUNCEMENT_ENABLE' 			=> $config['announcement_enable'],
		'ANNOUNCEMENT_ENABLE_GUESTS' 	=> $config['announcement_enable_guests'],
		'ANNOUNCEMENT_ALIGN'			=> $config['announcement_align'],
		'ANNOUNCEMENT_GUESTS_ALIGN'		=> $config['announcement_guests_align'],

		'ANNOUNCEMENT_SHOW_BIRTHDAYS_ALWAYS'		=> $config['announcement_show_birthdays_always'],
		'ANNOUNCEMENT_SHOW_BIRTHDAYS_AND_ANNOUNCE'	=> ($config['announcement_show_birthdays_and_announce']) ? true : false,

		'ANNOUNCEMENT_SHOW' 			=> $announcement_show,
		'ANNOUNCEMENT_SHOW_EVERYONE' 	=> $announcement_show_everyone_guests,
		'ANNOUNCEMENT_SHOW_BIRTHDAY'	=> ( $announcement_birthday_list != '' && $config['announcement_show_birthdays'] ) ? true : false,
		'ANNOUNCEMENT_BIRTHDAY_AVATAR'	=> ($config['announcement_birthday_avatar']) ? true : false,
		


));
}

/**
* prepares the preview announcement text
*/
function preview_announcement($text)
{
	$uid			= $bitfield			= $options	= '';	
	$allow_bbcode	= $allow_smilies	= true;
	$allow_urls		= false;
	//lets (mis)use generate_text_for_storage to create some uid, bitfield... for our preview
	generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
	//now we created it, lets show it
	$text			= generate_text_for_display($text, $uid, $bitfield, $options);
	
	return $text;
}

/**
* prepares the preview announcement text
*/
function announcement_post($where_sql, $order, $gotopost)
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $config;

	if (!class_exists('bbcode'))
	{
		include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
	}
	
	$bbcode_bitfield = '';
	$sql = 'SELECT *
		FROM  ' . POSTS_TABLE . " 
			$where_sql
		ORDER BY post_id $order";	
	$result = $db->sql_query_limit($sql, 1, 0);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if ( $row['post_attachment'] )
	{
			$sql = 'SELECT *
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_msg_id', $row['post_id']) . '
					AND in_message = 0
				ORDER BY filetime DESC, post_msg_id ASC';
			$result = $db->sql_query($sql);
	
			while ($row2 = $db->sql_fetchrow($result))
			{
				$attachments[$row2['post_msg_id']][] = $row2;
			}
			$db->sql_freeresult($result);
	}
	
	// Parse the message and subject
	$message = censor_text($row['post_text']);
	// Define the global bbcode bitfield, will be used to load bbcodes
	$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);
	
	// Instantiate BBCode if need be
	if ($bbcode_bitfield !== '')
	{
		$bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}
	
	// Second parse bbcode here
	if ($row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
	}
	
	$message = bbcode_nl2br($message);
	$message = smiley_text($message);
	
	if (!empty($attachments[$row['post_id']]))
	{
		parse_attachments($row['forum_id'], $message, $attachments[$row['post_id']], $update_count);
	}
		// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (!empty($attachments[$row['post_id']]))
	{
		foreach ($attachments[$row['post_id']] as $attachment)
		{
			$template->assign_block_vars('announcement_attachments', array(
				'DISPLAY_ATTACHMENTS'	=> $attachment)
			);
		}
	}

	// Assign index specific vars
	$template->assign_vars(array(		
		'S_HASATTACHMENTS'				=> (!empty($attachments[$row['post_id']])) ? true : false,
		'U_ANNOUNCEMENT_GOTOPOST'		=> ($gotopost) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id']) . '#p' . $row['post_id'] : '',


));	

	return $message;
}



?>