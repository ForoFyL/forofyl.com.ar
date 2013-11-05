<?php
/**
*
* @package phpBB3
* @version $Id: functions_mchat.php 1.3.5 2009-10-07 09:51:19Z rmcgirr83 $
* @copyright (c) 2009 phpbb3bbcodes.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* shamelessly stolen from functions.php
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Queries the session table to get information about online guests
* @param int $forum_id Limits the search to the forum with this id
* @return int The number of active distinct guest sessions
*/
function mchat_obtain_guest_count()
{
	global $db, $config, $phpEx;

	$reading_sql = ' AND s.session_page = "mchat.'.$phpEx.'"';

	$time = (time() - (intval($config['load_online_time']) * 60));

	// Get number of online guests

	if ($db->sql_layer === 'sqlite')
	{
		$sql = 'SELECT COUNT(session_ip) as num_guests
			FROM (
				SELECT DISTINCT s.session_ip
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) . 
				$reading_sql . '
			)';
	}
	else
	{
		$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_user_id = ' . ANONYMOUS . '
				AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
			$reading_sql;
	}
	$result = $db->sql_query($sql);
	$mchat_guests_online = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);

	return $mchat_guests_online;
}

/**
* Queries the session table to get information about online users
* @return array An array containing the ids of online, hidden and visible users, as well as statistical info
*/
function mchat_obtain_users_online()
{
	global $db, $config, $user, $phpEx;

	$mchat_online_users = array(
		'online_users'			=> array(),
		'hidden_users'			=> array(),
		'total_online'			=> 0,
		'visible_online'		=> 0,
		'hidden_online'			=> 0,
		'guests_online'			=> 0,
	);


	$mchat_online_users['guests_online'] = mchat_obtain_guest_count();

	// a little discrete magic to cache this for 30 seconds
	$time = (time() - (intval($config['load_online_time']) * 60));

	$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		FROM ' . SESSIONS_TABLE . ' s
		WHERE s.session_time >= ' . ($time - ((int) ($time % 30))) . '
		AND s.session_page = "mchat.'.$phpEx.'"
		AND s.session_user_id <> ' . ANONYMOUS;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// Skip multiple sessions for one user
		if (!isset($mchat_online_users['online_users'][$row['session_user_id']]))
		{
			$mchat_online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
			if ($row['session_viewonline'])
			{
				$mchat_online_users['visible_online']++;
			}
			else
			{
				$mchat_online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				$mchat_online_users['hidden_online']++;
			}
		}
	}
	$mchat_online_users['total_online'] = $mchat_online_users['guests_online'] + $mchat_online_users['visible_online'] + $mchat_online_users['hidden_online'];
	$db->sql_freeresult($result);

	return $mchat_online_users;
}

/**
* Uses the result of mchat_obtain_users_online to generate a localized, readable representation.
* @param mixed $mchat_online_users result of mchat_obtain_users_online - array with user_id lists for total, hidden and visible users, and statistics
* @return array An array containing the string for output to the template
*/
function mchat_obtain_users_online_string($mchat_online_users)
{
	global $config, $db, $user, $auth;

	$mchat_user_online_link = $mchat_online_userlist = '';

	if (sizeof($mchat_online_users['online_users']))
	{
		$sql = 'SELECT username, username_clean, user_id, user_type, user_allow_viewonline, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $mchat_online_users['online_users']) . '
				ORDER BY username_clean ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				if (isset($mchat_online_users['hidden_users'][$row['user_id']]))
				{
					$row['username'] = '<em>' . $row['username'] . '</em>';
				}

				if (!isset($mchat_online_users['hidden_users'][$row['user_id']]) || $auth->acl_get('u_viewonline'))
				{
					$mchat_user_online_link = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
					$mchat_online_userlist .= ($mchat_online_userlist != '') ? ', ' . $mchat_user_online_link : $mchat_user_online_link;
				}
			}
		}
		$db->sql_freeresult($result);
	}

	if (!$mchat_online_userlist)
	{
		$mchat_online_userlist = $user->lang['NO_ONLINE_USERS'];
	}

	$mchat_online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $mchat_online_userlist;

	// Build online listing
	$vars_online = array(
		'MCHAT_ONLINE'	=> array('total_online', 'l_t_user_s', 0),
		'MCHAT_REG'		=> array('visible_online', 'l_r_user_s', 0),
		'MCHAT_HIDDEN'	=> array('hidden_online', 'l_h_user_s', 1),
		'MCHAT_GUEST'		=> array('guests_online', 'l_g_user_s', 0)
	);

	foreach ($vars_online as $l_prefix => $var_ary)
	{
		if ($var_ary[2])
		{
			$l_suffix = '_AND';
		}
		else
		{
			$l_suffix = '';
		}
		switch ($mchat_online_users[$var_ary[0]])
		{
			case 0:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL' . $l_suffix];
			break;

			case 1:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL' . $l_suffix];
			break;

			default:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL' . $l_suffix];
			break;
		}
	}
	unset($vars_online);

	$l_online_users = sprintf($l_t_user_s, $mchat_online_users['total_online']);
	$l_online_users .= sprintf($l_r_user_s, $mchat_online_users['visible_online']);
	$l_online_users .= sprintf($l_h_user_s, $mchat_online_users['hidden_online']);

	$l_online_users .= sprintf($l_g_user_s, $mchat_online_users['guests_online']);

	return array(
		'online_userlist'	=> $mchat_online_userlist,
		'l_online_users'	=> $l_online_users,
	);
}

/**
* Generate output
*/
function mchat_users()
{
	global $db, $config, $template, $user;

	// Get users online list ... if required
	$l_online_users = $online_userlist = $l_online_record = '';

	$mchat_online_users = mchat_obtain_users_online();
	$mchat_user_online_strings = mchat_obtain_users_online_string($mchat_online_users);

	$l_online_users = $mchat_user_online_strings['l_online_users'];
	$mchat_online_userlist = $mchat_user_online_strings['online_userlist'];
	$mchat_total_online_users = $mchat_online_users['total_online'];


	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'MCHAT_TOTAL_USERS_ONLINE'		=> $l_online_users,
		'MCHAT_LOGGED_IN_USER_LIST'		=> $mchat_online_userlist,
	));

	return;
}

// mChat add-on Topic Notification
/**
* @param mixed $post_id limits deletion to a post_id in the forum
*/
function mchat_delete_topic($post_id)
{
	global $db;
	
	if (!isset($post_id) || empty($post_id))
	{
		return;
	}

	$sql = 'DELETE FROM ' . MCHAT_TABLE . ' WHERE post_id = ' . (int) $post_id;
	$db->sql_query($sql);

	return;
}

// mChat AutoPrune Chats
/**
* @param mixed $mchat_prune_amount set from mchat config entry
*/
function mchat_prune($mchat_prune_amount)
{
	global $db;
	// Run query to get the total message rows...
	$sql = 'SELECT COUNT(message_id) AS total_messages FROM ' . MCHAT_TABLE;
	$result = $db->sql_query($sql);
	$mchat_total_messages = (int) $db->sql_fetchfield('total_messages');
	$db->sql_freeresult($result);

	// count is below prune amount?
	// do nothing
	$prune = true;
	if ($mchat_total_messages <= $mchat_prune_amount)
	{
		$prune = false;
	}

	if ($prune)
	{

		$result = $db->sql_query_limit('SELECT * FROM '. MCHAT_TABLE . ' ORDER BY message_id ASC', 1);
		$row = $db->sql_fetchrow($result);
		$first_id = (int) $row['message_id'];
		
		$db->sql_freeresult($result);
		
		// compute the delete id 
		$delete_id = $mchat_total_messages - $mchat_prune_amount + $first_id;

		// let's go delete them...if the message id is less than the delete id
		$sql = 'DELETE FROM ' . MCHAT_TABLE . '
			WHERE message_id < ' . (int) $delete_id;
		$db->sql_query($sql);
	
		add_log('admin', 'LOG_MCHAT_TABLE_PRUNED');
	}
	// free up some memory...variable(s) are no longer needed.
	unset($mchat_total_messages);
	
	// return to what we were doing
	return;
		
}	
?>