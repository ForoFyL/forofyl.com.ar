<?php
/**
*
* @package phpBB3
* @author mtrs 29.06.2009
* @version $Id$ 1.0.0
* @copyrigh(c) 2008 mail codes by ameeck from notify admin registration mod
* @copyrigh(c) 2009 mtrs
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit();
}

define('IP_CHECK_DISABLE', 0);
define('IP_CHECK_NONE', 1);
define('IP_CHECK_LIGHT', 2);
define('IP_CHECK_FULL', 3);


function duplicate_ip_check($string)
{
	global $db, $template, $user, $config;

	if ($config['require_ip_check'] >= IP_CHECK_LIGHT)
	{
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_ip = "' . $db->sql_escape($user->ip) . '"
				AND user_type <> ' . USER_IGNORE;
	
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['user_id'];
		}
		$db->sql_freeresult($result);


		$sql = 'SELECT session_user_id
			FROM ' . SESSIONS_TABLE . '
			WHERE session_ip = "' . $db->sql_escape($user->ip) . '"
				AND session_user_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['session_user_id'];
		}
		$db->sql_freeresult($result);
	
		$sql = 'SELECT user_id
			FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE last_ip = "' . $db->sql_escape($user->ip) . '"
				AND user_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);
	}
	if ($config['require_ip_check'] == IP_CHECK_FULL)
	{
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['user_id'];
		}
		$db->sql_freeresult($result);
	
		$sql = 'SELECT user_id
			FROM ' . LOG_TABLE . '
			WHERE log_ip = "' . $db->sql_escape($user->ip) . '"
				AND user_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);	
	
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['user_id'];
		}
		$db->sql_freeresult($result);	
	
		$sql = 'SELECT poster_id
			FROM ' . POSTS_TABLE . '
			WHERE poster_ip = "' . $db->sql_escape($user->ip) . '"
				AND poster_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['poster_id'];
		}
		$db->sql_freeresult($result);	
	
		$sql = 'SELECT author_id
			FROM ' . PRIVMSGS_TABLE . '
			WHERE author_ip = "' . $db->sql_escape($user->ip) . '"
				AND author_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['author_id'];
		}
		$db->sql_freeresult($result);	
	
		$sql = 'SELECT vote_user_id
			FROM ' . POLL_VOTES_TABLE . '
			WHERE vote_user_ip = "' . $db->sql_escape($user->ip) . '"
				AND vote_user_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$dupe_ips[] = $row['vote_user_id'];
		}
		$db->sql_freeresult($result);
	}	
		
	$string = isset($dupe_ips) ? implode (',', array_unique($dupe_ips)) : '';
		
	if (strlen($string) > 8)
	{
		$string = substr($string, 0, 255);
	}
	
	return $string;
}

function dupe_usernames($string)
{
	global $db, $user, $auth, $template;
	
	if (!isset($string))
	{
		return false;
	}
	$user->add_lang('mods/dupe_ip');
	$dupe_users = false;
	
	$sql_in = explode(',',$string);

	$sql = 'SELECT user_id, username, user_colour
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $sql_in);
	$result = $db->sql_query($sql);	
	
	while($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('dupe_ip', array(
		'DUPE_USERNAME'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])
			));
		$dupe_users = true;
	}
	$template->assign_vars( array(
		'DUPLICATE_IP_EXISTS'	=> (!empty($string) && $dupe_users) ? true : false
			));
	$db->sql_freeresult($result);
	
	return true;	
}

function notify_admin_dupe_ips($data_username, $data_email, $user_regdate, $user_id, $dupe_user_ids)
{
	global $db, $auth, $template, $user, $phpbb_root_path, $phpEx, $config;
	
	if (empty($dupe_user_ids))
	{
		return false;
	}

	$dupe_users = explode(',', $dupe_user_ids);
	
	$sql = 'SELECT username
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $dupe_users);
	$result = $db->sql_query($sql);	
	
	$dupe_user_names = array();
	while($row = $db->sql_fetchrow($result))
	{
		$dupe_user_names[] = $row['username'];
	}
	$db->sql_freeresult($result);
	
	$dupe_users = implode(', ', $dupe_user_names);	

	$messenger = new messenger(false);

	// Codes below take from "Notify admin on registration MOD" by ameeck
		
	// Grab an array of user_id's with a_user permissions ... these users can activate a user
	$admin_ary = $auth->acl_get_list(false, 'a_user', false);
	$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

	// Also include founders
	$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

	if (sizeof($admin_ary))
	{
		$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
	}

	$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
		FROM ' . USERS_TABLE . ' ' .
		$where_sql;
	$result = $db->sql_query($sql);
		
	while ($row = $db->sql_fetchrow($result))
	{
		$messenger->template('admin_notify_duplicates', $row['user_lang']);
		$messenger->to($row['user_email'], $row['username']);
		$messenger->im($row['user_jabber'], $row['username']);

		$messenger->assign_vars(array(
			'USERNAME'			=> htmlspecialchars_decode($data_username),
			'DUPE_USERNAMES'	=> htmlspecialchars_decode($dupe_users),
			'U_USERNAME'		=> generate_board_url() . '/memberlist.' . $phpEx . '?mode=viewprofile&u=' . $user_id,
			'USER_MAIL'			=> $data_email,
			'USER_IP'			=> $user->ip,
			'USER_REGDATE'		=> date($config['default_dateformat'], $user_regdate))
		);

		$messenger->send($row['user_notify_type']);
	}
	$db->sql_freeresult($result);		
}

?>